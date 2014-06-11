<?php
 if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

require_once('include/api/SugarApi.php');

class SubscriptionsApi extends SugarApi
{

    public function registerApiRest()
    {
        return array(
            'subscribeToRecord' => array(
                'reqType' => 'POST',
                'path' => array('<module>','?', 'subscribe'),
                'pathVars' => array('module','record'),
                'method' => 'subscribeToRecord',
                'shortHelp' => 'This method subscribes the user to the current record, for activity stream updates.',
                'longHelp' => 'modules/ActivityStream/clients/base/api/help/recordSubscribe.html',
            ),
            'unsubscribeFromRecord' => array(
                'reqType' => 'DELETE',
                'path' => array('<module>','?', 'unsubscribe'),
                'pathVars' => array('module','record'),
                'method' => 'unsubscribeFromRecord',
                'shortHelp' => 'This method unsubscribes the user from the current record, for activity stream updates.',
                'longHelp' => 'modules/ActivityStream/clients/base/api/help/recordUnsubscribe.html',
            )
        );
    }

    public function subscribeToRecord(ServiceBase $api, array $args)
    {
        $this->requireArgs($args, array('module', 'record'));
        $bean = BeanFactory::retrieveBean($args['module'], $args['record']);

        if (empty($bean)) {
            throw new SugarApiExceptionNotFound('Could not find parent record '.$args['record'].' in module '.$args['module']);
        }

        if (!$bean->ACLAccess('view')) {
            $moduleName = null;
            if (isset($args['module'])) {
                $failed_module_strings = return_module_language($GLOBALS['current_language'], $args['module']);
                $moduleName = $failed_module_strings['LBL_MODULE_NAME'];
            }
            $args = null;
            if (!empty($moduleName)) {
                $args = array('moduleName' => $moduleName);
            }
            throw new SugarApiExceptionNotAuthorized('EXCEPTION_SUBSCRIBE_MODULE_NOT_AUTHORIZED', $args);
        }

        return Subscription::subscribeUserToRecord($api->user, $bean);
    }

    public function unsubscribeFromRecord(ServiceBase $api, array $args)
    {
        $this->requireArgs($args, array('module', 'record'));
        $bean = BeanFactory::retrieveBean($args['module'], $args['record']);

        if (empty($bean)) {
            throw new SugarApiExceptionNotFound('Could not find parent record '.$args['record'].' in module '.$args['module']);
        }

        if (!$bean->ACLAccess('view')){
            $moduleName = null;
            if (isset($args['module'])) {
                $failed_module_strings = return_module_language($GLOBALS['current_language'], $args['module']);
                $moduleName = $failed_module_strings['LBL_MODULE_NAME'];
            }
            $args = null;
            if (!empty($moduleName)) {
                $args = array('moduleName' => $moduleName);
            }
            throw new SugarApiExceptionNotAuthorized('EXCEPTION_SUBSCRIBE_MODULE_NOT_AUTHORIZED', $args);
        }

        return Subscription::unsubscribeUserFromRecord($api->user, $bean);
    }
}
