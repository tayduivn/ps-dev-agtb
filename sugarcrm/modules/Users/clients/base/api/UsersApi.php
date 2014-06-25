<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2014 SugarCRM Inc.  All rights reserved.
 */

require_once('clients/base/api/ModuleApi.php');

class UsersApi extends ModuleApi
{
    public function registerApiRest()
    {
        return array(
            'delete' => array(
                'reqType'   => 'DELETE',
                'path'      => array('Users', '?'),
                'pathVars'  => array('module', 'record'),
                'method'    => 'deleteUser',
                'shortHelp' => 'This method deletes a User record',
                'longHelp'  => 'modules/Users/api/help/UsersApi.html',
            ),
        );
    }

    /**
     * Delete the user record and set the appropriate flags. Handled in a separate api call from the base one because
     * the base api delete field doesn't set user status to 'inactive' or employee_status to 'Terminated'
     *
     * The non-api User deletion logic is handled in /modules/Users/controller.php::action_delete()
     *
     * @param  RestService $api
     * @param  array       $args
     * @return array
     */
    public function deleteUser($api, $args)
    {
        // Ensure we have admin access to this module
        if (!($api->user->isAdmin() || $api->user->isAdminForModule('Users'))) {
            throw new SugarApiExceptionNotAuthorized();
        }

        // This logic is also present in /module/Users/controller.php::action_delete()
        if ($api->user->id === $args['record']) {
            throw new SugarApiExceptionInvalidParameter();
        }

        $this->requireArgs($args, array('module', 'record'));
        // loadBean() handles exceptions for bean validation
        $user = $this->loadBean($api, $args, 'delete');
        $user->deleted = 1;
        $user->status = 'Inactive';
        $user->employee_status = 'Terminated';
        $user->save();

        return array('id' => $user->id);
    }
}
