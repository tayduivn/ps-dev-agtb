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
     * To be used from logic hooks to index a bean.
     *
     * @param \SugarDav $bean
     * @param string $event Triggered event
     * @param array $arguments Optional arguments
     * @return void
     */
    public function run($bean, $event, $arguments)
    {
        $adapter = $this->getAdapterFactory();
        $manager = $this->getManager();
        if ($bean instanceof \CalDavEvent) {
            if ($bean->parent_type != $bean->module_name &&
                $adapter->getAdapter($bean->getBean()->module_name)) {
                $fetchedRow = $this->getBeanFetchedRow($bean);
                $bean->clearVCalendarEvent();
                $manager->calDavImport($fetchedRow, $bean->module_name);
            }
        } elseif ($bean instanceof \SugarBean) {
            //@TODO check if bean is childred
            if ($adapter->getAdapter($bean->module_name)) {
                $fetchedRow = $this->getBeanFetchedRow($bean);
                $manager->calDavExport($fetchedRow, $bean->module_name);
            }
        }
    }

    /**
     * Retrieve bean fetched row
     * @param \SugarBean $bean
     * @return array
     */
    protected function getBeanFetchedRow(\SugarBean $bean)
    {
        if (!$bean->fetched_row) {
            $bean->retrieve($bean->id);
        }

        return $bean->fetched_row;
    }

    /**
     * function return manager object for handler processing
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


    /**
     * @param \SugarBean $bean
     * @return null|\SugarBean
     */
    protected function getParentBean($bean)
    {
        return \BeanFactory::getBean($bean->module_name, $bean->repeat_parent_id);
    }
}
