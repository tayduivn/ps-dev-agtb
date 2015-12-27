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
        /** @var \User $user */
        $user = $GLOBALS['current_user'];

        /** @var \CalDavEventCollection $calDavBean */
        $calDavBean = \BeanFactory::getBean('CalDavEvents', $this->processedData[0][0]);

        if (!$calDavBean->isImportable()) {
            $calDavBean->getSynchronizationObject()->setJobCounter();
            return \SchedulersJob::JOB_CANCELLED;
        }

        if ($this->setJobToEnd($calDavBean)) {
            return \SchedulersJob::JOB_CANCELLED;
        }

        $bean = $calDavBean->getBean();
        if (!$bean) {
            $bean = \BeanFactory::getBean($user->getPreference('caldav_module'));
            $bean->id = create_guid();
            $bean->new_with_id = true;
            if ($bean instanceof \Call) {
                $bean->direction = $user->getPreference('caldav_call_direction');
            }
            $calDavBean->setBean($bean);
            $calDavBean->save();
        }

        $adapterFactory = $this->getAdapterFactory();
        $adapter = $adapterFactory->getAdapter($bean->module_name);
        if (!$adapter) {
            throw new JQLogicException('Bean ' . $bean->module_name . ' does not have CalDav adapter');
        }

        if ($adapter->import($this->processedData, $bean)) {
            $bean->save();
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
        $jqManager->calDavImport($this->processedData, $this->saveCounter);
    }
}
