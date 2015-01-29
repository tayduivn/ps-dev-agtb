<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

require_once('PMSEHandlers/PMSEHookHandler.php');

class PMSELogicHook
{
    function before_save($bean, $event, $arguments)
    {
        if (!$this->isSugarInstalled()) {
            return true;
        }

        if (!$this->isExpectedModule($bean)) {
            return true;
        }
        //Define PA Hook Handler
        $hookHandler = new PMSEHookHandler();
        //Define if this is a new record or an updated record
        $isNewRecord = empty($bean->fetched_row['id']);
        return $hookHandler->runStartEventBeforeSave($bean, $event, $arguments, array(), $isNewRecord);
    }

    function after_save($bean, $event, $arguments)
    {
        if (!$this->isSugarInstalled()) {
            return true;
        }

        if (!$this->isExpectedModule($bean)) {
            return true;
        }
        //Define PA Hook Handler
        $handler = new PMSEHookHandler();
        return $handler->runStartEventAfterSave($bean, $event, $arguments);
    }

    function after_delete($bean, $event, $arguments)
    {
        if (!$this->isSugarInstalled()) {
            return true;
        }

        if (!$this->isExpectedModule($bean)) {
            return true;
        }
        //Define PA Hook Handler
        $handler = new PMSEHookHandler();
        return $handler->terminateCaseAfterDelete($bean, $event, $arguments);
    }

    private function isExpectedModule($bean)
    {
        include('PMSEModules.php');
        $pmseModulesList = (isset($pmseModulesList)) ? $pmseModulesList : array();
        //returns immediately if the bean is a common module
        $result = true;
        //Modules that will not be processed by PA
        $blacklistedModules = array(
            'Teams',
            'Users',
            'UserPreferences',
            'Subscriptions',
            'OAuthToken',
            'Dashboards',
            'Activities',
            'Filters',
            'ACLAction',
            'SessionManager',
            'vCal',
            'TeamSetModule',
            'ForecastWorksheet',
            'ACLField',
            'ACLRole',
            'DocumentRevision',
            'SavedReport',
            'ForecastManagerWorksheet',
            'KBDocumentRevision',
            'KBContent',
            'KBDocumentKBTag',
            'EmailTemplate',
            'TeamMembership',
            'TaxRate',
        );
        if (isset($bean->module_name)) {
            $excludedModules = array_merge($blacklistedModules, $pmseModulesList);
            if (in_array($bean->module_name, $excludedModules) OR in_array($bean->object_name, $excludedModules)) {
                return false;
            }
        }

        return $result;
    }

    private function isSugarInstalled()
    {
        //TODO: Need to find a way to test that Sugar is correctly installed
        return true;
    }
}
