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
        $adapterFactory = $this->getAdapterFactory();
        $bean = \BeanFactory::getBean($this->processedData[0][0]);
        if (!($bean instanceof \SugarBean)) {
            throw new JQInvalidArgumentException('Bean must be an instance of SugarBean. Instance of ' .
                get_class($bean) . ' given');
        }
        $bean->id = $this->processedData[0][1];
        $adapter = $adapterFactory->getAdapter($bean->module_name);
        if (!$adapter) {
            throw new JQLogicException('Bean ' . $bean->module_name . ' does not have CalDav adapter');
        }
        $handler = $this->getHandler();
        $calDavBean = $handler->getDavBean($bean);

        if ($this->setJobToEnd($calDavBean)) {
            return \SchedulersJob::JOB_CANCELLED;
        }

        $importData = array();
        HookHandler::$importHandler = function($data, $collection) use (&$importData) {
            $importData = $data;
        };
        if ($adapter->export($this->processedData, $calDavBean)) {
            $calDavBean->save();
            $importData = $adapter->verifyImportAfterExport($this->processedData, $importData, $calDavBean);
            if ($importData) {
                $saveCounter = $calDavBean->getSynchronizationObject()->setSaveCounter();
                $this->getManager()->calDavImport($importData, $saveCounter);
            }
        }

        $calDavBean->getSynchronizationObject()->setJobCounter();

        return \SchedulersJob::JOB_SUCCESS;
    }

    /**
     * @inheritdoc
     */
    protected function reschedule()
    {
        $jqManager = $this->getManager();
        $jqManager->calDavExport($this->processedData, $this->saveCounter);
    }
}
