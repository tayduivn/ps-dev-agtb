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

namespace Sugarcrm\Sugarcrm\Security\Validator\Constraints\Mvc;

use Sugarcrm\Sugarcrm\Security\Validator\Constraints\Bean\ModuleNameValidator as BeanModuleNameValidator;

/**
 *
 * Legacy module name validator. Note that currently any available module value
 * will return a positive result. This validator currently does not take into
 * account the module which are somehow disabled.
 *
 */
class ModuleNameValidator extends BeanModuleNameValidator
{
    /**
     * List of all available modules.
     * @var array
     */
    protected $moduleList = array();

    /**
     * List of explicit module name we allow which cannot
     * be resolved otherwise.
     * @var array
     */
    protected $explicitModules = array(
        'app_strings',
    );

    /**
     * Ctor
     */
    public function __construct()
    {
        $this->moduleList = $this->getModulesFromGlobals();
    }

    /**
     * Get list of modules as available in the globals
     * @return array
     */
    protected function getModulesFromGlobals()
    {
        global $moduleList, $modInvisList;
        return array_merge($moduleList, $modInvisList);
    }

    /**
     * Check if module exists
     * @param string $value
     * @return boolean
     */
    protected function isValidModule($module)
    {
        // try beans first
        if (parent::isValidModule($module)) {
            return true;
        }

        // fallback to explicit modules
        if (in_array($module, $this->explicitModules)) {
            return true;
        }

        // last resort list
        if (in_array($module, $this->moduleList)) {
            return true;
        }

        return false;
    }
}
