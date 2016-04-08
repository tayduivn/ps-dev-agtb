<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\Sugarcrm\Dav\Cal\JobQueue;

use SugarCache;
use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory as CalDavAdapterFactory;
use Sugarcrm\Sugarcrm\Dav\Cal\Hook\Handler as HookHandler;
use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\AdapterInterface;
use Sugarcrm\Sugarcrm\JobQueue\Exception\LogicException as JQLogicException;
use Sugarcrm\Sugarcrm\JobQueue\Handler\RunnableInterface;
use Sugarcrm\Sugarcrm\Dav\Cal\Hook\Notifier\ListenerInterface;
use Sugarcrm\Sugarcrm\Dav\Cal\JobQueue\HookListener\ExportListener;
use Sugarcrm\Sugarcrm\Dav\Cal\JobQueue\HookListener\ImportListener;

class Handler implements RunnableInterface
{
    /** @var string */
    protected $eventId = '';

    /**
     * @var ListenerInterface|null
     */
    protected $listener = null;

    /**
     * @var HookHandler
     */
    protected $hookHandler;

    /**
     * Handler constructor.
     *
     * @param string $eventId
     */
    public function __construct($eventId)
    {
        $this->eventId = $eventId;
    }

    /**
     * Start handler process for current CalDavEventCollection object.
     *
     * @throws \Sugarcrm\Sugarcrm\JobQueue\Exception\LogicException if related bean doesn't have adapter
     * @return string
     */
    public function run()
    {
        /** @var \User $currentUser */
        $currentUser = $GLOBALS['current_user'];

        /** @var \CalDavEventCollection $calDavBean */
        $calDavBean = \BeanFactory::getBean('CalDavEvents', $this->eventId, array(
            'strict_retrieve' => true,
            'deleted' => false
        ));

        if (!$calDavBean) {
            return \SchedulersJob::JOB_FAILURE;
        }

        /** @var \CalDavQueue $queueBean */
        $queueBean = $calDavBean->getQueueObject();

        $this->hookHandler = new HookHandler();

        /** @var \CalDavQueue $queueItem */
        while ($queueItem = $queueBean->findFirstQueued($calDavBean->id)) {
            $GLOBALS['current_user'] = \BeanFactory::getBean('Users', $queueItem->created_by);
            $conflictCounter = $calDavBean->getSynchronizationObject()->getConflictCounter();
            if ($conflictCounter && $queueItem->save_counter < $conflictCounter) {
                $calDavBean->getSynchronizationObject()->setJobCounter();
                $queueItem->status = \CalDavQueue::STATUS_COMPLETED;
                $queueItem->save();
                continue;
            }

            try {
                switch ($queueItem->action) {
                    case \CalDavQueue::ACTION_IMPORT:
                        $this->import($calDavBean, $queueItem);
                        break;
                    case \CalDavQueue::ACTION_EXPORT:
                        $this->export($calDavBean, $queueItem);
                        break;
                    default:
                        $queueItem->status = \CalDavQueue::STATUS_COMPLETED;
                        $queueItem->save();
                        continue;
                }

                $calDavBean->getSynchronizationObject()->setConflictCounter(false);
            } catch (\Exception $e) {
                if ($this->listener instanceof ExportListener) {
                    $this->hookHandler->getExportNotifier()->detach($this->listener);
                } elseif ($this->listener instanceof ImportListener) {
                    $this->hookHandler->getImportNotifier()->detach($this->listener);
                }
                $this->hookHandler->export($calDavBean->getBean(), false, true);
            }

            $calDavBean->getSynchronizationObject()->setJobCounter();
            $queueItem->status = \CalDavQueue::STATUS_COMPLETED;
            $queueItem->save();
            $calDavBean->retrieve(-1, true, false);
        }

        $GLOBALS['current_user'] = $currentUser;
        return \SchedulersJob::JOB_SUCCESS;
    }

    /**
     * Start import process.
     *
     * @param \CalDavEventCollection $calDavBean
     * @param \CalDavQueue $queueItem
     */
    protected function import(\CalDavEventCollection $calDavBean, \CalDavQueue $queueItem)
    {
        $bean = $calDavBean->getBean();

        if (!$bean) {
            /** @var \User $user */
            $user = $GLOBALS['current_user'];
            if (!$calDavBean->parent_type) {
                $calDavBean->parent_type = $user->getPreference('caldav_module');
            }

            $bean = \BeanFactory::getBean($calDavBean->parent_type);
            $bean->id = create_guid();
            $bean->send_invites_uid = $calDavBean->event_uid;
            $bean->new_with_id = true;
            if ($bean instanceof \Call) {
                // Because handler is running as daemon script as part of queueManager.php
                // we can't use cache local store because updates from CalDav settings page
                // will not update this local store inside daemon, only external storage.
                $oldValue = SugarCache::instance()->useLocalStore;
                SugarCache::instance()->useLocalStore = false;
                $bean->direction = $user->getPreference('caldav_call_direction');
                SugarCache::instance()->useLocalStore = $oldValue;
            }
            $calDavBean->setBean($bean);
            $calDavBean->save();
        }

        $bean->send_invites_uid = $calDavBean->event_uid;

        $adapter = $this->getAdapterFactory()->getAdapter($bean->module_name);
        if (!$adapter) {
            throw new JQLogicException('Bean ' . $bean->module_name . ' does not have CalDav adapter');
        }

        $importData = json_decode($queueItem->data, true);
        
        $this->listener = new ExportListener($bean);
        $this->hookHandler->getExportNotifier()->attach($this->listener);
        
        $bean = $adapter->getBeanForImport($bean, $calDavBean, $importData);
        $result = $adapter->import($importData, $bean);
        if ($result != AdapterInterface::NOTHING) {
            switch ($result) {
                case AdapterInterface::SAVE :
                    if (!empty($bean->repeat_parent_id)) {
                        \Activity::disable();
                    }
                    $bean->save();
                    if (!empty($bean->repeat_parent_id)) {
                        \Activity::enable();
                    }
                    break;
                case AdapterInterface::DELETE :
                    $bean->mark_deleted($bean->id);
                    break;
                case AdapterInterface::RESTORE :
                    $bean->mark_undeleted($bean->id);
                    $bean->save();
                    break;
            }
            $exportDataSet = $this->listener->getDataSet();
            $this->hookHandler->getExportNotifier()->detach($this->listener);
            if (!$exportDataSet) {
                $exportDataSet = array(array());
            }
            foreach ($exportDataSet as $exportData) {
                $exportData = $adapter->verifyExportAfterImport($importData, $exportData, $bean);
                if ($exportData) {
                    $saveCounter = $calDavBean->getSynchronizationObject()->setSaveCounter();
                    $calDavBean->getQueueObject()->export($exportData, $saveCounter);
                }
            }
        } else {
            $this->hookHandler->getExportNotifier()->detach($this->listener);
        }
    }

    /**
     * Start export process.
     *
     * @param \CalDavEventCollection $calDavBean
     * @param \CalDavQueue $queueItem
     */
    protected function export(\CalDavEventCollection $calDavBean, \CalDavQueue $queueItem)
    {
        $bean = $calDavBean->getBean();
        $adapter = $this->getAdapterFactory()->getAdapter($bean->module_name);

        if (!$adapter) {
            throw new JQLogicException('Bean ' . $bean->module_name . ' does not have CalDav adapter');
        }

        $exportData = json_decode($queueItem->data, true);

        $this->listener = new ImportListener($calDavBean);
        $this->hookHandler->getImportNotifier()->attach($this->listener);

        $result = $adapter->export($exportData, $calDavBean);
        if ($result != AdapterInterface::NOTHING) {
            switch ($result) {
                case AdapterInterface::SAVE :
                    $calDavBean->save();
                    break;
                case AdapterInterface::DELETE :
                    $calDavBean->mark_deleted($calDavBean->id);
                    break;
                case AdapterInterface::RESTORE :
                    $calDavBean->mark_undeleted($calDavBean->id);
                    $calDavBean->save();
                    break;
            }
            $importDataSet = $this->listener->getDataSet();
            $this->hookHandler->getImportNotifier()->detach($this->listener);
            if (!$importDataSet) {
                $importDataSet = array(array());
            }
            foreach ($importDataSet as $importData) {
                $importData = $adapter->verifyImportAfterExport($exportData, $importData, $calDavBean);
                if ($importData) {
                    $saveCounter = $calDavBean->getSynchronizationObject()->setSaveCounter();
                    $calDavBean->getQueueObject()->import($importData, $saveCounter);
                }
            }
        } else {
            $this->hookHandler->getExportNotifier()->detach($this->listener);
        }
    }

    /**
     * Get CalDav adapter.
     *
     * @return \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory
     */
    protected function getAdapterFactory()
    {
        return CalDavAdapterFactory::getInstance();
    }
}
