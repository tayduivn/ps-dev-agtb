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

namespace Sugarcrm\Sugarcrm\Dav\Cal\Hook;

use \Sugarcrm\Sugarcrm\JobQueue\Manager\Manager as JQManager;
use \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory as CalDavAdapterFactory;

/**
 *
 * Logic hook handler
 *
 */
class Handler
{
    /**
     * @var callable
     */
    public static $importHandler = null;

    /**
     * @var callable
     */
    public static $exportHandler = null;

    /**
     * @param \CalDavEventCollection $collection
     * @param string $calDavData
     * @return bool success of operation
     */
    public function import(\CalDavEventCollection $collection, $calDavData)
    {
        if (!$collection->isImportable() || !$collection->parent_type) {
            return false;
        }

        $adapter = $this->getAdapterFactory()->getAdapter($collection->parent_type);
        if (!$adapter) {
            return false;
        }

        $preparedData = $adapter->prepareForImport($collection, $calDavData);
        if (!$preparedData) {
            return false;
        }

        $continue = true;
        if (is_callable(static::$importHandler)) {
            $continue = call_user_func_array(static::$importHandler, array(
                $collection->module_name,
                $collection->id,
                $preparedData,
            ));
        }
        if ($continue && $preparedData) {
            $saveCounter = $collection->getSynchronizationObject()->setSaveCounter();
            $this->getManager()->calDavImport($collection->module_name, $collection->id, $preparedData, $saveCounter);
        }
        static::$importHandler = null;
        return true;
    }

    /**
     * @param \SugarBean $bean
     * @param array $changedFields
     * @param array $invitesBefore
     * @param array $invitesAfter
     * @param bool $insert
     * @return bool success of operation
     */
    public function export(
        \SugarBean $bean,
        $changedFields = array(),
        $invitesBefore = array(),
        $invitesAfter = array(),
        $insert = false
    ) {
        $adapter = $this->getAdapterFactory()->getAdapter($bean->module_name);
        if (!$adapter) {
            return false;
        }

        $preparedData = $adapter->prepareForExport(
            $bean,
            $changedFields,
            $invitesBefore,
            $invitesAfter,
            $insert
        );
        if (!$preparedData) {
            return false;
        }

        if (!empty($bean->repeat_parent_id)) {
            $parentBeanId = $bean->repeat_parent_id;
        } else {
            $parentBeanId = $bean->id;
        }

        /** @var \CalDavEventCollection $collection */
        $collection = \BeanFactory::getBean('CalDavEvents');
        $collection = $collection->findByBean($bean);
        if (!$collection) {
            $collection = \BeanFactory::getBean('CalDavEvents');
            $collection->setBean($bean);
            $collection->save();
        }

        $continue = true;
        if (is_callable(static::$exportHandler)) {
            $continue = call_user_func_array(static::$exportHandler, array(
                $bean->module_name,
                $parentBeanId,
                $preparedData,
            ));
        }
        if ($continue && $preparedData) {
            $saveCounter = $collection->getSynchronizationObject()->setSaveCounter();
            $this->getManager()->calDavExport($bean->module_name, $parentBeanId, $preparedData, $saveCounter);
        }
        static::$exportHandler = null;
        return true;
    }

    /**
     * Get manager object for handler processing.
     * @return \Sugarcrm\Sugarcrm\JobQueue\Manager\Manager
     */
    protected function getManager()
    {
        return new JQManager();
    }

    /**
     * @return \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory
     */
    protected function getAdapterFactory()
    {
        return CalDavAdapterFactory::getInstance();
    }
}
