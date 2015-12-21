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

namespace Sugarcrm\Sugarcrm\Dav\Cal;

use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory as CalDavAdapaterFactory;

/**
 * Class Handler
 * Export/import bean
 * @package Sugarcrm\Sugarcrm\Dav\Cal
 */
class Handler
{
    /**
     * Run export action
     * @param array $exportData
     * @deprecated The whole class is planned to be eliminated. Do export via Adapter.
     */
    public function export($exportData)
    {
        list($beanData, $changedFields, $invites) = $exportData;
        list($beanModuleName, $beanId, $repeatParentId, $childEventsId, $isUpdated) = $beanData;
        $adapterFactory = $this->getAdapterFactory();
        if ($adapter = $adapterFactory->getAdapter($beanModuleName)) {
            $bean = \BeanFactory::getBean($beanModuleName, $beanId);
            $calDavBean = $this->getDavBean($bean);
            if ($adapter->export($exportData, $calDavBean)) {
                $calDavBean->save();
            }
        }
    }

    /**
     * Get CalDav bean object
     * @param \SugarBean $bean
     * @return \CalDavEventCollection
     */
    public function getDavBean(\SugarBean $bean)
    {
        /** @var \CalDavEventCollection $event */
        $event = \BeanFactory::getBean('CalDavEvents');
        $related = $event->findByBean($bean);

        if ($related) {
            return $related;
        }

        $event->setBean($bean);
        $event->save();
        return $event;
    }

    /**
     * Get Sugar Bean object, except CalDav
     * @param \CalDavEventCollection $calDavBean
     * @return \SugarBean
     */
    public function getSugarBean(\CalDavEventCollection $calDavBean)
    {
        global $current_user;

        if (!$calDavBean->parent_id && $calDavBean->id) {
            $dbBean = \BeanFactory::getBean($calDavBean->module_name, $calDavBean->id);
            $calDavBean->parent_type = $dbBean->parent_type;
            $calDavBean->parent_id = $dbBean->parent_id;
        }

        $bean = $calDavBean->getBean();

        if (is_null($bean)) {
            $moduleName = $current_user->getPreference('caldav_module');
            $bean = \BeanFactory::getBean($moduleName);
            if ($moduleName == 'Calls') {
                $bean->direction = $current_user->getPreference('caldav_call_direction');
            }
            $bean->id = create_guid();
            $bean->new_with_id = true;
            $calDavBean->setBean($bean);
            $calDavBean->save();
        }
        return $bean;
    }

    /**
     * Run import action
     * @param array $importData
     * @return bool
     */

    public function import($importData)
    {
        list($beanData, $changedFields, $invites) = $importData;
        $calDavBean = \BeanFactory::getBean('CalDavEvents', $beanData[0]);
        $bean = $this->getSugarBean($calDavBean);
        $adapterFactory = $this->getAdapterFactory();
        if ($adapter = $adapterFactory->getAdapter($bean->module_name)) {
            if ($adapter->import($importData, $calDavBean)) {
                $bean->save();
            }
        }

        return true;
    }

    /**
     * @return \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory
     */
    protected function getAdapterFactory()
    {
        return CalDavAdapaterFactory::getInstance();
    }

    /**
     * @param \SugarBean $bean
     * @return null|\SugarBean
     */
    protected function getParentBean($bean)
    {
        return \BeanFactory::getBean($bean->module_name, $bean->repeat_parent_id);
    }

    /**
     * Check if bean is child using repeat_parent_id
     * @param \SugarBean $bean
     * @return bool
     */
    protected function isBeanChild($bean)
    {
        return $bean->repeat_parent_id ? true : false;
    }

    /**
     * Return CalDavEventCollection bean
     * @return \CalDavEventCollection
     */
    protected function getCalDavEvent()
    {
        return \BeanFactory::getBean('CalDavEvents');
    }
}
