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

use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Registry as CalDavAdapterRegistry;
use Sugarcrm\Sugarcrm\Dav\Cal\Hook\Handler as HookHandler;
use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\DataAdapterInterface;
use Sugarcrm\Sugarcrm\JobQueue\Handler\RunnableInterface;
use Sugarcrm\Sugarcrm\Dav\Cal\Hook\Notifier\ListenerInterface;
use Sugarcrm\Sugarcrm\Dav\Cal\JobQueue\HookListener\ExportListener;
use Sugarcrm\Sugarcrm\Dav\Cal\JobQueue\HookListener\ImportListener;
use Sugarcrm\Sugarcrm\Logger\LoggerTransition;

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
     * @var LoggerTransition
     */
    protected $logger;

    /**
     * Handler constructor.
     *
     * @param string $eventId
     */
    public function __construct($eventId)
    {
        $this->eventId = $eventId;
        $this->logger = new LoggerTransition(\LoggerManager::getLogger());
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
            $this->logger->warning("CalDav: CalDavEvent bean for Event($this->eventId) not found");
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
                $this->logger->debug(
                    "CalDav: Queue item save_counter = $queueItem->save_counter, conflict_counter = $conflictCounter"
                );
                $calDavBean->getSynchronizationObject()->setJobCounter();
                $queueItem->status = \CalDavQueue::STATUS_CONFLICT;
                $queueItem->save();
                continue;
            }
            $queueItem->status = \CalDavQueue::STATUS_COMPLETED;
            try {
                switch ($queueItem->action) {
                    case \CalDavQueue::ACTION_IMPORT:
                        $this->logger->debug('CalDav: Job performs import action');
                        $this->import($calDavBean, $queueItem);
                        break;
                    case \CalDavQueue::ACTION_EXPORT:
                        $this->logger->debug('CalDav: Job performs export action');
                        $this->export($calDavBean, $queueItem);
                        break;
                    default:
                        $this->logger->debug('CalDav: Job just resolves queue item with status complete');
                        $queueItem->save();
                        continue;
                }

                $this->logger->debug('CalDav: Job run: intializing conflict_solver');
                $calDavBean->getSynchronizationObject()->setConflictCounter(false);
            } catch (\Exception $e) {
                $this->logger->notice('CalDav: Job triggered expected exception: ' . $e->getMessage());
                if ($this->listener instanceof ExportListener) {
                    $this->hookHandler->getExportNotifier()->detach($this->listener);
                } elseif ($this->listener instanceof ImportListener) {
                    $this->hookHandler->getImportNotifier()->detach($this->listener);
                }
                $bean = $calDavBean->getBean();
                $this->logger->debug("CalDav: Conflict. Job sends {$bean->module_name}({$bean->id}) to export");
                $queueItem->status = \CalDavQueue::STATUS_CONFLICT;
                $this->hookHandler->export($bean, false, true);
            }

            $this->logger->debug('Setting job_counter and saving queue item');
            $calDavBean->getSynchronizationObject()->setJobCounter();
            $queueItem->save();
            $calDavBean->retrieve(-1, true, false);

            \BeanFactory::clearCache();
        }

        $this->logger->debug("CalDav: Job sets global current user to User($currentUser->id)");
        $GLOBALS['current_user'] = $currentUser;

        $this->logger->debug('CalDav: Job was successfully completed');
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
        $this->logger->debug("CalDav: Job Queue Handler: Start import. CalDav bean id = {$calDavBean->id}");

        $bean = $calDavBean->getBean();

        if (!$bean) {
            if ($calDavBean->deleted) {
                return;
            }
            /** @var \User $user */
            $user = $GLOBALS['current_user'];
            if (!$calDavBean->parent_type) {
                $calDavBean->parent_type = $user->getPreference('caldav_module');
            }

            $bean = \BeanFactory::getBean($calDavBean->parent_type);
            $bean->id = create_guid();
            $bean->send_invites_uid = $calDavBean->event_uid;
            $bean->new_with_id = true;

            $adapterFactory = $this->getAdapterRegistry()->getFactory($bean->module_name);
            if (!$adapterFactory) {
                $this->logger->debug("CalDav: Adapter factory for module $bean->module_name has not found");
                return;
            }
            $adapterFactory->getPropertiesAdapter()->setBeanProperties($bean, $calDavBean, $user);

            $calDavBean->setBean($bean);
            $calDavBean->save();
        }
        $this->logger->debug("CalDav: Importing event's Sugar Bean is {$bean->module_name}({$bean->id})");
        $adapterFactory = $this->getAdapterRegistry()->getFactory($bean->module_name);
        if (!$adapterFactory) {
            $this->logger->debug("CalDav: Adapter factory for module $bean->module_name has not found");
            return;
        }

        $adapter = $adapterFactory->getAdapter();

        $importData = json_decode($queueItem->data, true);

        $this->listener = new ExportListener($bean);
        $this->hookHandler->getExportNotifier()->attach($this->listener);

        $bean = $adapter->getBeanForImport($bean, $calDavBean, $importData);

        $bean->send_invites_uid = $calDavBean->event_uid;
        $this->logger->debug("CalDav: Set bean's send_invites_uid to event_uid = {$calDavBean->event_uid}");

        $result = $adapter->import($importData, $bean);
        if ($result != DataAdapterInterface::NOTHING) {
            switch ($result) {
                case DataAdapterInterface::SAVE:
                    $this->logger->debug("CalDav: import via adapter resulted in 'bean should be saved'");
                    if (!empty($bean->repeat_parent_id)) {
                        $this->logger->debug('CalDav: temporarily disable Activity Stream for child event');
                        \Activity::disable();
                    }
                    $bean->save();
                    if (!empty($bean->repeat_parent_id)) {
                        \Activity::enable();
                    }
                    break;
                case DataAdapterInterface::DELETE:
                    $this->logger->debug("CalDav: import via adapter resulted in 'bean should be deleted'");
                    $bean->mark_deleted($bean->id);
                    break;
                case DataAdapterInterface::RESTORE:
                    $this->logger->debug("CalDav: import via adapter resulted in 'bean should be restored'");
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
                    $this->logger->debug(
                        'CalDav: JQ Handler Import: there is exportData, setting save_counter and do export'
                    );
                    $saveCounter = $calDavBean->getSynchronizationObject()->setSaveCounter();
                    $calDavBean->getQueueObject()->export($exportData, $saveCounter);
                }
            }
        } else {
            $this->logger->debug("CalDav: import via adapter resulted in 'nothing should be saved'");
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
        $this->logger->debug("CalDav: Job Queue Handler: Start export. CalDav bean id = {$calDavBean->id}");

        $bean = $calDavBean->getBean();
        $this->logger->debug("CalDav: Expoting event's Sugar Bean is {$bean->module_name}({$bean->id})");

        $adapterFactory = $this->getAdapterRegistry()->getFactory($bean->module_name);
        if (!$adapterFactory) {
            $this->logger->debug("CalDav: Adapter factory for module $bean->module_name has not found");
            return;
        }

        $adapter = $adapterFactory->getAdapter();

        $exportData = json_decode($queueItem->data, true);

        $this->listener = new ImportListener($calDavBean);
        $this->hookHandler->getImportNotifier()->attach($this->listener);

        $result = $adapter->export($exportData, $calDavBean);
        if ($result != DataAdapterInterface::NOTHING) {
            switch ($result) {
                case DataAdapterInterface::SAVE:
                    $this->logger->debug("CalDav: export via adapter resulted in 'bean should be saved'");
                    $calDavBean->save();
                    break;
                case DataAdapterInterface::DELETE:
                    $this->logger->debug("CalDav: export via adapter resulted in 'bean should be deleted'");
                    $calDavBean->mark_deleted($calDavBean->id);
                    break;
                case DataAdapterInterface::RESTORE:
                    $this->logger->debug("CalDav: export via adapter resulted in 'bean should be restored'");
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
                    $this->logger->debug(
                        'CalDav: JQ Handler Export: there is importData, setting save_counter and do import'
                    );
                    $saveCounter = $calDavBean->getSynchronizationObject()->setSaveCounter();
                    $calDavBean->getQueueObject()->import($importData, $saveCounter);
                }
            }
        } else {
            $this->logger->debug("CalDav: export via adapter resulted in 'nothing should be saved'");
            $this->hookHandler->getExportNotifier()->detach($this->listener);
        }
    }

    /**
     * Factory method for Adapter Registry.
     *
     * @return \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Registry
     */
    protected function getAdapterRegistry()
    {
        return CalDavAdapterRegistry::getInstance();
    }
}
