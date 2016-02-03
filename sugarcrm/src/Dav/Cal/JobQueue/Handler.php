<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\Sugarcrm\Dav\Cal\JobQueue;

use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory as CalDavAdapterFactory;
use Sugarcrm\Sugarcrm\Dav\Cal\Hook\Handler as HookHandler;
use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\AdapterInterface;
use Sugarcrm\Sugarcrm\JobQueue\Exception\LogicException as JQLogicException;
use Sugarcrm\Sugarcrm\JobQueue\Handler\RunnableInterface;

class Handler implements RunnableInterface
{
    /** @var string */
    protected $eventId = '';

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

        /** @var \CalDavQueue $queueItem */
        while ($queueItem = $queueBean->findFirstQueued($calDavBean->id)) {
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
                HookHandler::$importHandler = null;

                $hookHandler = new HookHandler();
                $hookHandler->export($calDavBean->getBean(), false, true);
            }

            $calDavBean->getSynchronizationObject()->setJobCounter();
            $queueItem->status = \CalDavQueue::STATUS_COMPLETED;
            $queueItem->save();
        }

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
            $bean->new_with_id = true;
            if ($bean instanceof \Call) {
                $bean->direction = $user->getPreference('caldav_call_direction');
            }
            $calDavBean->setBean($bean);
            $calDavBean->save();
        }

        $adapter = $this->getAdapterFactory()->getAdapter($bean->module_name);
        if (!$adapter) {
            throw new JQLogicException('Bean ' . $bean->module_name . ' does not have CalDav adapter');
        }

        $importData = json_decode($queueItem->data, true);

        $bean = $adapter->getBeanForImport($bean, $calDavBean, $importData);
        $result = $adapter->import($importData, $bean);
        if ($result != AdapterInterface::NOTHING) {
            $exportDataSet = array();
            HookHandler::$exportHandler = function($beanModule, $beanId, $data) use ($bean, &$exportDataSet) {
                if (!empty($bean->repeat_parent_id)) {
                    $parentBeanId = $bean->repeat_parent_id;
                } else {
                    $parentBeanId = $bean->id;
                }
                if ($bean->module_name == $beanModule && $parentBeanId == $beanId) {
                    $exportDataSet[] = $data;
                    return false;
                }
                return true;
            };
            switch ($result) {
                case AdapterInterface::SAVE :
                    $bean->save();
                    break;
                case AdapterInterface::DELETE :
                    $bean->mark_deleted($bean->id);
                    break;
                case AdapterInterface::RESTORE :
                    $bean->mark_undeleted($bean->id);
                    $bean->save();
                    break;
            }
            HookHandler::$exportHandler = null;
            foreach ($exportDataSet as $exportData) {
                $exportData = $adapter->verifyExportAfterImport($importData, $exportData, $bean);
                if ($exportData) {
                    $saveCounter = $calDavBean->getSynchronizationObject()->setSaveCounter();
                    $calDavBean->getQueueObject()->export($importData, $saveCounter);
                }
            }
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

        $result = $adapter->export($exportData, $calDavBean);
        if ($result != AdapterInterface::NOTHING) {
            $importDataSet = array();
            HookHandler::$importHandler = function ($beanModule, $beanId, $data) use ($calDavBean, &$importDataSet) {
                if ($calDavBean->module_name == $beanModule && $calDavBean->id == $beanId) {
                    $importDataSet[] = $data;
                    return false;
                }
                return true;
            };
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
            HookHandler::$importHandler = null;
            foreach ($importDataSet as $importData) {
                $importData = $adapter->verifyImportAfterExport($exportData, $importData, $calDavBean);
                if ($importData) {
                    $saveCounter = $calDavBean->getSynchronizationObject()->setSaveCounter();
                    $calDavBean->getQueueObject()->import($importData, $saveCounter);
                }
            }
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
