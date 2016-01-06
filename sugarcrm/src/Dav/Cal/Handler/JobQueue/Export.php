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

namespace Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue;

use Sugarcrm\Sugarcrm\JobQueue\Exception\LogicException as JQLogicException;
use Sugarcrm\Sugarcrm\JobQueue\Exception\InvalidArgumentException as JQInvalidArgumentException;
use Sugarcrm\Sugarcrm\Dav\Cal\Hook\Handler as HookHandler;

/**
 * Class Export
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue
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
        /** @var \CalDavEventCollection $collection */
        $collection = \BeanFactory::getBean('CalDavEvents');
        $calDavBean = $collection->findByParentModuleAndId($this->beanModule, $this->beanId);
        if (!$calDavBean) {
            return \SchedulersJob::JOB_FAILURE;
        }

        if ($this->setJobToEnd($calDavBean)) {
            return \SchedulersJob::JOB_CANCELLED;
        }

        $status = \SchedulersJob::JOB_SUCCESS;
        try {
            if ($adapter->export($this->processedData, $calDavBean)) {
                $importData = array();
                HookHandler::$importHandler = function ($beanModule, $beanId, $data) use ($calDavBean, &$importData) {
                    if ($calDavBean->module_name == $beanModule && $calDavBean->id == $beanId) {
                        $importData = $data;
                        return false;
                    }
                    return true;
                };
                $calDavBean->save();
                HookHandler::$importHandler = null;
                if ($importData) {
                    $importData = $adapter->verifyImportAfterExport($this->processedData, $importData, $calDavBean);
                }
                if ($importData) {
                    $saveCounter = $calDavBean->getSynchronizationObject()->setSaveCounter();
                    $this->getManager()->calDavImport($calDavBean->module_name, $calDavBean->id, $importData, $saveCounter);
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
