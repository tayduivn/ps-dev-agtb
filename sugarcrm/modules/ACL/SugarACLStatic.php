<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * Static ACL implementation - ACLs defined per-module
 * Uses ACLController and ACLAction
 */
class SugarACLStatic extends SugarACLStrategy
{
    /**
     * (non-PHPdoc)
     * @see SugarACLStrategy::checkAccess()
     */
    public function checkAccess($module, $action, $context)
    {
        //BEGIN SUGARCRM flav=pro ONLY
        // Check if we have to apply team security based on ACLs
        // If user had admin rights then team security is disabled
        if($action == "team_security") {
            if(isset($context['bean']) && $context['bean']->bean_implements('ACL')) {
                $user_id = $this->getUserID($context);
                if(ACLAction::getUserAccessLevel($user_id, $module, 'access') != ACL_ALLOW_ENABLED) {
                    return true;
                }
                if(ACLAction::getUserAccessLevel($user_id, $module, 'admin') == ACL_ALLOW_ADMIN
                    || ACLAction::getUserAccessLevel($user_id, $module, 'admin') == ACL_ALLOW_ADMIN_DEV) {
                        // disable team security for admins
                        return false;
                    }
                return true;
            } else {
                // True means team security is enabled and it's the default
                return true;
            }
        }
        //END SUGARCRM flav=pro ONLY
        $user = $this->getCurrentUser($context);
        if($user && $user->isAdminForModule($module)) {
            return true;
        }

        $action = strtolower($action);

        if($action == "field") {
            return $this->fieldACL($module, $context['action'], $context);
        }

        if(!empty($context['bean'])) {
            return $this->beanACL($module, $action, $context);
        }

        if($module == 'Trackers') {
            return ACLController::checkAccessInternal($module, $action, true, 'Tracker');
        }

        return ACLController::checkAccessInternal($module, $action, !empty($context['owner_override']));
    }

    static $action_translate = array(
        'listview' => 'list',
        'index' => 'list',
//        'popupeditview' => 'edit',
//        'editview' => 'edit',
        'detail' => 'view',
        'detailview' => 'view',
        'save' => 'edit',
    );

    /**
     * Check access to fields
     * @param string $module
     * @param string $action
     * @param array $context
     */
    protected function fieldACL($module, $action, $context)
    {
        $bean = isset($context['bean'])?$context['bean']:null;
        $is_owner = false;
        if(!empty($context['owner_override'])) {
            $is_owner = $context['owner_override'];
        } else {
            if($bean) {
                // non-ACL bean - access granted
                if(!$bean->bean_implements('ACL')) return true;
                $is_owner = $bean->isOwner($this->getUserID($context));
            }
        }

        if(!$this->getUserID($context)) return true;

        $field_access = ACLField::hasAccess($context['field'], $module, $this->getUserID($context),  $is_owner);

        switch($action) {
            case 'access':
                return $field_access > 0;
            case 'read':
            case 'detail':
                $access = 1;
                break;
            case 'write':
            case 'edit':
                $access = 3;
                break;
            default:
                $access = 4;
        }

        return ($field_access == 4 || $field_access == $access);
    }

    /**
     * Check bean ACLs
     * @param string $module
     * @param string $action
     * @param array $context
     */
    protected function beanACL($module, $action, $context)
    {
        $bean = $context['bean'];
        //if we don't implent acls return true
        if(!$bean->bean_implements('ACL')) return true;

        if(!empty($context['owner_override'])) {
            $is_owner = $context['owner_override'];
        } else {
            $is_owner = $bean->isOwner($this->getUserID($context));
        }

        if(isset(self::$action_translate[$action])) {
            $action = self::$action_translate[$action];
        }

        switch ($action)
        {
            case 'import':
            case 'list':
                return ACLController::checkAccessInternal($module, $action, true);
            case 'delete':
            case 'view':
            case 'export':
                return ACLController::checkAccessInternal($module, $action, $is_owner);
            case 'edit':
                if(!isset($context['owner_override']) && !empty($bean->id)) {
                    if(!empty($bean->fetched_row) && !empty($bean->fetched_row['id']) && !empty($bean->fetched_row['assigned_user_id']) && !empty($bean->fetched_row['created_by'])){
                        $temp = BeanFactory::newBean($bean->module_dir);
                        $temp->populateFromRow($bean->fetched_row);
                    }else{
                        if($bean->new_with_id) {
                            $is_owner = true;
                        } else {
                            $temp = BeanFactory::getBean($bean->module_dir, $bean->id);
                            if(!empty($temp)) {
                                $is_owner = $temp->isOwner($this->getUserID($context));
                            }
                            unset($temp);
                        }
                    }
                }
            case 'popupeditview':
            case 'editview':
                return ACLController::checkAccessInternal($module,'edit', $is_owner);
        }
        //if it is not one of the above views then it should be implemented on the page level
        return true;

    }

    public function checkFieldList($module, $field_list, $action, $context)
    {
        $user_id = $this->getUserID($context);
        if(is_admin($GLOBALS['current_user']) || empty($user_id) || empty($_SESSION['ACL'][$user_id][$module]['fields'])) {
            return array();
        }
        return parent::checkFieldList($module, $field_list, $action, $context);
    }

    public function getFieldListAccess($module, $field_list, $context)
    {
        $user_id = $this->getUserID($context);
        if(is_admin($GLOBALS['current_user']) || empty($user_id) || empty($_SESSION['ACL'][$user_id][$module]['fields'])) {
        	return array();
        }
        return parent::getFieldListAccess($module, $field_list, $action, $context);
    }
}
