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
use Sugarcrm\Sugarcrm\Dav\Cal\Hook\Handler as HookHandler;

/**
 * Class Import
 * @package Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue
 * Class for import process initialization
 */
class Import extends Base
{
    /**
     * start imports process for current CalDavEventCollection object
     * @throws \Sugarcrm\Sugarcrm\JobQueue\Exception\LogicException if related bean doesn't have adapter
     * @return string
     */
    public function run()
    {
        /** @var \CalDavEventCollection $calDavBean */
        $calDavBean = \BeanFactory::getBean($this->beanModule, $this->beanId, array(
            'strict_retrieve' => true,
        ));
        if (!$calDavBean instanceof \CalDavEventCollection) {
            return \SchedulersJob::JOB_CANCELLED;
        }

        if ($this->setJobToEnd($calDavBean)) {
            return \SchedulersJob::JOB_CANCELLED;
        }

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

        $status = \SchedulersJob::JOB_SUCCESS;
        $bean = $adapter->getBeanForImport($bean, $calDavBean, $this->processedData);
        try {
            $liveBean = clone $bean;
            if ($adapter->import($this->processedData, $liveBean)) {
                $exportData = array();
                HookHandler::$exportHandler = function($beanModule, $beanId, $data) use ($bean, &$exportData) {
                    if (!empty($bean->repeat_parent_id)) {
                        $parentBeanId = $bean->repeat_parent_id;
                    } else {
                        $parentBeanId = $bean->id;
                    }
                    if ($bean->module_name == $beanModule && $parentBeanId == $beanId) {
                        $exportData = $data;
                        return false;
                    }
                    return true;
                };
                $liveBean->save();
                HookHandler::$exportHandler = null;
                if ($exportData) {
                    $exportData = $adapter->verifyExportAfterImport($this->processedData, $exportData, $liveBean);
                }
                if ($exportData) {
                    $saveCounter = $calDavBean->getSynchronizationObject()->setSaveCounter();
                    $this->getManager()->calDavExport($liveBean->module_name, $liveBean->id, $exportData, $saveCounter);
                }
            }
            $bean = $liveBean;
        } catch (\Exception $exception) {
            HookHandler::$exportHandler = null;
            $status = \SchedulersJob::JOB_FAILURE;
            $hookHandler = new HookHandler();
            $hookHandler->export($bean, false, true);
        }
        $calDavBean->getSynchronizationObject()->setJobCounter();
        return $status;
    }

    /**
     * @inheritdoc
     */
    protected function reschedule()
    {
        $jqManager = $this->getManager();
        $jqManager->calDavImport($this->beanModule, $this->beanId, $this->processedData, $this->saveCounter);
    }
}
