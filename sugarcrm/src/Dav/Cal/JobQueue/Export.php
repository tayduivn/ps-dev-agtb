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

use Sugarcrm\Sugarcrm\JobQueue\Exception\LogicException as JQLogicException;
use Sugarcrm\Sugarcrm\Dav\Cal\Hook\Handler as HookHandler;
use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\AdapterInterface;

/**
 * Class Export
 * @package Sugarcrm\Sugarcrm\Dav\Cal\JobQueue
 * Class for export process initialization
 */
class Export extends Base
{
    /**
     * start export process for current bean if it extends from SugarBean
     * @throws \Sugarcrm\Sugarcrm\JobQueue\Exception\InvalidArgumentException if bean not instance of SugarBean
     * @throws \Sugarcrm\Sugarcrm\JobQueue\Exception\LogicException if bean doesn't have adapter
     * @return string
     */
    public function run()
    {
        $adapter = $this->getAdapterFactory()->getAdapter($this->beanModule);
        if (!$adapter) {
            throw new JQLogicException('Bean ' . $this->beanModule . ' does not have CalDav adapter');
        }
        /** @var \CalDavEventCollection $calDavBean */
        $calDavBean = \BeanFactory::getBean('CalDavEvents');
        $calDavBean = $calDavBean->findByParentModuleAndId($this->beanModule, $this->beanId);
        if (!$calDavBean) {
            return \SchedulersJob::JOB_FAILURE;
        }

        if ($this->setJobToEnd($calDavBean)) {
            return \SchedulersJob::JOB_CANCELLED;
        }

        $status = \SchedulersJob::JOB_SUCCESS;
        try {
            $exportData = $this->processedData;
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
                        $this->getManager()
                             ->calDavImport($calDavBean->module_name, $calDavBean->id, $importData, $saveCounter);
                    }
                }
            }
        } catch (\Exception $exception) {
            HookHandler::$importHandler = null;
            $status = \SchedulersJob::JOB_FAILURE;
            $hookHandler = new HookHandler();
            $hookHandler->export(\BeanFactory::getBean($this->beanModule, $this->beanId), false, true);
        }
        $calDavBean->getSynchronizationObject()->setJobCounter();
        $calDavBean->getSynchronizationObject()->setConflictCounter(false);
        return $status;
    }

    /**
     * @inheritdoc
     */
    protected function reschedule()
    {
        $jqManager = $this->getManager();
        $jqManager->calDavExport($this->beanModule, $this->beanId, $this->processedData, $this->saveCounter);
    }
}
