<?php
if (!defined('sugarEntry') || !sugarEntry)
    die('Not A Valid Entry Point');
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

require_once ('modules/ModuleBuilder/parsers/ModuleBuilderParser.php');

class ParserModifyPortalConfig extends ModuleBuilderParser
{

    /**
     * Constructor
     */
    function init()
    {
    }

    /**
     * handles portal config save and creating of portal users
     */
    function handleSave()
    {
        $portalFields = array('on','appStatus', 'appName', 'logoURL', 'serverUrl', 'maxQueryResult', 'fieldsToDisplay', 'maxSearchQueryResult');
        $portalConfig = array(
            'platform' => 'portal',
            'debugSugarApi' => true,
            'logLevel' => array(
                'name' => 'DEBUG',
                'value' => 2
            ),
            'logWriter' => 'ConsoleWriter',
            'logFormatter' => 'SimpleFormatter',
            'metadataTypes' => array(),
            'displayModules' => array(
                'Bugs',
                'Cases',
                'KBDocuments'
            ),
            'serverTimeout' => 30,
            'defaultModule' => 'Cases',
            'orderByDefaults' => array(
                'Cases' => array(
                    'field' => 'case_number',
                    'direction' => 'desc'
                ),
                'Bugs' => array(
                    'field' => 'bug_number',
                    'direction' => 'desc'
                ),
                'Notes' => array(
                    'field' => 'date_modified',
                    'direction' => 'desc'
                ),
                'KBDocuments' => array(
                    'field' => 'date_modified',
                    'direction' => 'desc'
                ) 
            )
        );
        foreach ($portalFields as $field) {
            if (isset($_REQUEST[$field])) {
                $portalConfig[$field] = $_REQUEST[$field];
            }
        }

        if (isset($portalConfig['appStatus']) && $portalConfig['appStatus'] == 'true') {
            $portalConfig['appStatus'] = 'online';
            $portalConfig['on'] = 1;
        } else {
            $portalConfig['appStatus'] = 'offline';
        }

        foreach ($portalConfig as $fieldKey => $fieldValue) {
            $GLOBALS ['system_config']->saveSetting('portal', $fieldKey, json_encode($fieldValue));
        }

        if (isset($portalConfig['on']) && $portalConfig['on'] == 1) {
            $u = $this->getPortalUser();
            $role = $this->getPortalACLRole();

            if (!($u->check_role_membership($role->name))) {
                $u->load_relationship('aclroles');
                $u->aclroles->add($role);
                $u->save();
            }
        }
    }

    /**
     * Creates Portal User
     * @return User
     */
    function getPortalUser()
    {
        $portalUserName = "SugarCustomerSupportPortalUser";
        $id = User::retrieve_user_id($portalUserName);
        if (!$id) {
            $user = new User();
            $user->user_name = $portalUserName;
            $user->title = 'Sugar Customer Support Portal User';
            $user->description = $user->title;
            $user->first_name = "";
            $user->last_name = $user->title;
            $user->status = 'Active';
            $user->receive_notifications = 0;
            $user->is_admin = 0;
            $random = time() . mt_rand();
            $user->authenicate_id = md5($random);
            $user->user_hash = User::getPasswordHash($random);
            $user->default_team = '1';
            $user->created_by = '1';
            $user->portal_only = '1';
            $user->save();
            $id = $user->id;
        }
        $resultUser = new User();
        $resultUser->retrieve($id);
        return $resultUser;
    }

    /**
     * Creates Portal role and sets ACLS
     * @return ACLRole
     */
    function getPortalACLRole()
    {
        $allowedModules = array('Bugs', 'Cases', 'Notes', 'KBDocuments', 'Contacts');
        $allowedActions = array('edit','admin', 'access', 'list', 'view');
        $role = new ACLRole();
        $role->retrieve_by_string_fields(array('name' => 'Customer Self-Service Portal Role'));
        if (empty($role->id)) {
            $role->name = "Customer Self-Service Portal Role";
            $role->description = "Customer Self-Service Portal Role";
            $role->save();
            $roleActions = $role->getRoleActions($role->id);
            foreach ($roleActions as $moduleName => $actions) {
                // enable allowed moduels
                if (isset($actions['module']['access']['id']) && !in_array($moduleName, $allowedModules)) {
                    $role->setAction($role->id, $actions['module']['access']['id'], ACL_ALLOW_DISABLED);
                } elseif (isset($actions['module']['access']['id']) && in_array($moduleName, $allowedModules)) {
                    $role->setAction($role->id, $actions['module']['access']['id'], ACL_ALLOW_ENABLED);
                } else {
                    foreach ($actions as $action => $actionName) {
                        if (isset($actions[$action]['access']['id'])) {
                            $role->setAction($role->id, $actions[$action]['access']['id'], ACL_ALLOW_DISABLED);
                        }
                    }
                }
                if (in_array($moduleName, $allowedModules)) {
                    $role->setAction($role->id, $actions['module']['access']['id'], ACL_ALLOW_ENABLED);
                    $role->setAction($role->id, $actions['module']['admin']['id'], ACL_ALLOW_ALL);
                    foreach ($actions['module'] as $actionName => $action) {
                        if (in_array($actionName, $allowedActions)) {
                            $aclAllow = ACL_ALLOW_ALL;
                        } else {
                            $aclAllow = ACL_ALLOW_NONE;
                        }
                        if ($moduleName == 'KBDocuments' && $actionName == 'edit') {
                            $aclAllow = ACL_ALLOW_NONE;
                        }
                        if ($moduleName == 'Contacts') {
                            if ($actionName == 'edit' || $actionName == 'view') {
                                $aclAllow = ACL_ALLOW_OWNER;
                            } else {
                                $aclAllow = ACL_ALLOW_NONE;
                            }

                        }
                        if ($actionName != 'access') {
                            $role->setAction($role->id, $action['id'], $aclAllow);
                        }

                    }
                }
            }
        }
        return $role;
    }

}

?>
