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

use Sugarcrm\Sugarcrm\JobQueue\Manager\Manager;
use Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory;

class Handler
{
    /**
     * Run export action
     * @param \SugarBean $bean
     */
    public function export(\SugarBean $bean)
    {
        $adapter = $this->getAdapterFactory();
        if (!($adapter->getAdapter($bean->module_name))) {
            throw new \Exception('Can not find adapater for module ' . $bean->module_name);
        }
        $manager = $this->getManager();
        $manager->registerHandler('CalDavExport', 'Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue\Export');
        $manager->CalDavExport($bean);
        $manager->run();
    }

    /**
     * Get CalDav bean object
     * @return \CalDavEvent
     */

    public function getDavBean()
    {

    }

    /**
     * Get Sugar Bean object, except CalDav
     * @return \SugarBean
     */
    public function getSugarBean()
    {

    }

    /**
     * Run import action
     * @param \CalDavEvent $bean
     */

    public function import(\CalDavEvent $bean)
    {
        $manager = $this->getManager();
        $manager->registerHandler('CalDavImport', 'Sugarcrm\Sugarcrm\Dav\Cal\Handler\JobQueue\Import');
        $manager->CalDavImport($bean);
        $manager->run();
    }

    /**
     * function return manager object for handler processing
     * @return Manager
     */
    protected function getManager()
    {
        return new Manager();
    }

    /**
     * @return \Sugarcrm\Sugarcrm\Dav\Cal\Adapter\Factory
     */
    protected function getAdapterFactory()
    {
        return Factory::getInstance();
    }
}
