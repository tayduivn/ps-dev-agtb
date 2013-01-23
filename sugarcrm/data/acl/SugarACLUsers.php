<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
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
require_once('data/SugarACLStrategy.php');

class SugarACLUsers extends SugarACLStrategy
{
    /**
     * Check access a current user has on Users and Employees
     * @param string $module
     * @param string $view
     * @param array $context
     * @return bool|void
     */
    public function checkAccess($module, $view, $context) {
        if($module != 'Users' && $module != 'Employees') {
            // how'd you get here...
            return false;
        }

        if ( $view == 'team_security' ) {
            // Let the other modules decide
            return true;
        }

        $current_user = $this->getCurrentUser($context);

        $bean = self::loadBean($module, $context);
        $myself = !empty($bean->id) && $bean->id == $current_user->id;

        // Let's make it a little easier on ourselves and fix up the actions nice and quickly
        $view = SugarACLStrategy::fixUpActionName($view);
        if ( $view == 'field' ) {
            $context['action'] = SugarACLStrategy::fixUpActionName($context['action']);
        }

        // even an admin can't delete themselves
        if( $myself ) {
            if ( $view == 'delete') {
                // Here's the obvious way to disable yourself
                return false;
            }
            if ( $view == 'field' 
                 && ( $context['action'] == 'edit' || $context['action'] == 'massupdate' || $context['action'] == 'delete' )
                 && ( $context['field'] == 'employee_status' || $context['field'] == 'status' ) ) {
                // This is another way to disable yourself
                return false;
            }
        }
            

        if($current_user->isAdminForModule($module)) {
            return true;
        }

        if(empty($view) || empty($current_user->id)) {
            return true;
        }

        // We can edit ourself
        if( $myself && $view == 'edit' ) {
            return true;
        }

        if ( !$myself && $view == 'field' && in_array($context['field'],array('system_generated_password','pwd_last_changed','authenticate_id','sugar_login','external_auth_only')) ) {
            // This isn't us, these aren't fields we should be poking around in.
            return false;
        }

        if($view == 'view' || $view == 'ListView' || $view == 'list' || $view == 'export' || $view == 'Export' || $view == 'field' || $view == 'DetailView' || $view == 'detail' || $view == 'team_security' ) {
            if($view == 'field' && ($context['field'] == 'password' || $context['field'] == 'user_hash') ) {
                return false;
            }
            if( $view == 'field'
                && ($context['action'] == 'edit' || $context['action'] == 'massupdate' || $context['action'] == 'delete')
                && in_array($context['field'],array('is_admin','is_group','user_name','user_type','portal_only','show_on_employees','status','employee_status','title','department','reports_to_id','reports_to_name','system_generated_password','external_auth_only','sugar_login','authenticate_id','pwd_last_changed')) ) {
                return false;
            }
            return true;
        }

        return false;
    }

    /**
     * Load bean from context
     * @static
     * @param string $module
     * @param array $context
     * @return SugarBean
     */
    protected static function loadBean($module, $context = array()) {
        if(isset($context['bean']) && $context['bean'] instanceof SugarBean && $context['bean']->module_dir == $module) {
            $bean = $context['bean'];
        } else {
            $bean = BeanFactory::newBean($module);
        }
        return $bean;
    }

}
