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

require_once 'data/SugarACLStrategy.php';
require_once 'modules/ACL/SugarACLStatic.php';

/**
 * Generic ACL implementation
 * @api
 */
class SugarACL
{
    static $acls = array();

    // Access levels for field
    // matches ACLField::hasAccess returns for compatibility
    const ACL_NO_ACCESS = 0;
    const ACL_READ_ONLY = 1;
    const ACL_READ_WRITE = 4;

    /**
     * Load ACLs for module
     * @param string $module
     * @return array ACLs list
     */
    public static function loadACLs($module)
    {
        if(!isset(self::$acls[$module])) {
            self::$acls[$module] = array();
            if(isset($GLOBALS['dictionary'][$module]['acls'])) {
                $acl_list = $GLOBALS['dictionary'][$module]['acls'];
            } else {
                $acl_list = array();
            }
            $bean = BeanFactory::newBean($module);
            foreach($bean->defaultACLs() as $defacl) {
                if(isset($acl_list[$defacl])) {
                    continue;
                }
                $acl_list[$defacl] = true;
            }

            foreach($acl_list as $klass => $args) {
                if($args === false) continue;
                self::$acls[$module][] = new $klass($args);
            }
        }

        return self::$acls[$module];
    }

    /**
     * Check ACLs for field
     * @param string $module
     * @param string $field
     * @param string $action
     * @param array $context
     * @return bool Access allowed?
     */
    public static function checkField($module, $field, $action,  $context = array())
    {
        $context['field'] = $field;
        $context['action'] = $action;
        return self::checkAccess($module, "field", $context);
    }

    /**
     * Get ACL access level
     * @param string $module
     * @param string $field
     * @param array $context
     * @return int Access level - one of ACL_* constants
     */
    public static function getFieldAccess($module, $field, $context = array())
    {
        $read = self::checkField($module, $field, "detail", $context);
        if(!$read) return self::ACL_NO_ACCESS;
        $write = self::checkField($module, $field, "edit", $context);
        if($write) return self::ACL_READ_WRITE;
        return self::ACL_READ_ONLY;
    }

    /**
     * Check access
     * @param string $module
     * @param string $action
     * @param array $context
     * @return bool Access allowed?
     */
    public static function checkAccess($module, $action, $context = array())
    {
        foreach($this->loadACLs($module) as $acl) {
            if(!$acl->checkAccess($module, $action, $context)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get list of disabled modules
     * @param array $list Module list
     * @param string $action
     * @return array Disabled modules
     */
    public static function disabledModuleList($list, $action = 'list')
    {
        $result = array();
        foreach($list as $key => $module) {
            if(!self::checkAccess($module, $action)) {
                $result[$key] = $module;
            }
        }
        return $result;
    }

    /**
     * Remove disabled modules from list
     * @param array $list Module list
     * @param string $action
     * @param bool $use_value Use value or key as module name?
     * @return array Filtered list
     */
    public static function filterModuleList($list, $action = 'access', $use_value = true)
    {
        $result = array();
        foreach($list as $key => $module) {
            if(self::checkAccess($use_value?$module:$key, $action)) {
                $result[$key] = $module;
            }
        }
        return $result;
    }

}