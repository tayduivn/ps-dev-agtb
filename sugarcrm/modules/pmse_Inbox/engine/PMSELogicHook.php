<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

use Sugarcrm\Sugarcrm\ProcessManager;

require_once 'modules/pmse_Inbox/engine/PMSEEngineUtils.php';

class PMSELogicHook
{
    function after_save($bean, $event, $arguments)
    {
        if (!$this->isSugarInstalled()) {
            return true;
        }

        if (!PMSEEngineUtils::hasActiveProcesses($bean)) {
            return true;
        }
        //Define PA Hook Handler
        $handler = ProcessManager\Factory::getPMSEObject('PMSEHookHandler');
        return $handler->runStartEventAfterSave($bean, $event, $arguments);
    }

    function after_delete($bean, $event, $arguments)
    {
        if (!$this->isSugarInstalled()) {
            return true;
        }

        if (!PMSEEngineUtils::hasActiveProcesses($bean)) {
            return true;
        }
        //Define PA Hook Handler
        $handler = ProcessManager\Factory::getPMSEObject('PMSEHookHandler');
        return $handler->terminateCaseAfterDelete($bean, $event, $arguments);
    }

    protected function isSugarInstalled()
    {
        global $sugar_config;

        return empty($GLOBALS['installing']) && empty($sugar_config['installer_locked']);
    }
}
