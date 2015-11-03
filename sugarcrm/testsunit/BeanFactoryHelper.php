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

namespace Sugarcrm\SugarcrmTestsUnit;

/**
 *
 * Helper class initialize BeanFactory.
 *
 * TODO: Refactor beanfactory to remove all global references.
 *
 */
class BeanFactoryHelper
{
    /**
     * @var BeanFactoryHelper
     */
    protected static $instance;

    /**
     * Do not instantiate directly
     */
    private function __construct()
    {
    }

    /**
     * Get helper instance
     * @return BeanFactoryHelper
     */
    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Reload BeanFactory settings
     */
    public function reload()
    {
        // FIXME: remove globals from BeanFactory to avoid this. Eventually
        // tests can setup/teardown their globals. For now lets set the
        // following globals.
        global $objectList, $moduleList, $modInvisList;

        include 'include/modules.php';

        // Use bean classes overwrite to set the list
        \BeanFactory::$bean_classes = $beanList;
    }
}
