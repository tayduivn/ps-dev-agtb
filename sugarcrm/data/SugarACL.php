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
     * Load bean from context
     * @static
     * @param $module
     * @param array $context
     * @return SugarBean
     */
    protected static function loadBean($module, $context = array())
    {
        if(isset($context['bean']) && $context['bean'] instanceof SugarBean && $context['bean']->module_dir == $module) {
            $bean = $context['bean'];
        } else {
            $bean = BeanFactory::newBean($module);
        }
        return $bean;
    }


    /**
     * Reset ACL cache
     * To be used when
     * @param string $module If empty, all ACL module caches are reset
     */
    public static function resetACLs($module = null)
    {
        if($module) {
            unset(self::$acls[$module]);
        } else {
            self::$acls = array();
        }
    }

    /**
     * Load ACLs for module
     * @param string $module
     * @param array $context
     * @return array ACLs list
     */
    public static function loadACLs($module, $context = array())
    {
        if(!isset(self::$acls[$module])) {
            self::$acls[$module] = array();

            $bean = self::loadBean($module, $context);

            // Be sure we got a SugarBean:
            // Some modules do not extend SugarBean (ie DynamicFields)
            // TODO: see how we can support ACLs for those too
            if(! $bean instanceof SugarBean) {
                return array();
            }

            $acl_list = $bean->defaultACLs();

            foreach($acl_list as $klass => $args) {
                if($args === false) continue;
                self::$acls[$module][] = new $klass($args);
            }
        }

        return self::$acls[$module];
    }

    /**
     * Check if module has any ACLs defined
     * @param string $module
     * @return bool
     */
    public static function moduleSupportsACL($module)
    {
        $acls = self::loadACLs($module);
        return !empty($acls);
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
        $context['field'] = strtolower($field);
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
        if(!isset(self::$acls[$module])) {
            self::loadACLs($module, $context);
        }
        foreach(self::$acls[$module] as $acl) {
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
     * @param bool $use_value Use value or key as module name?
     * @return array Disabled modules
     */
    public static function disabledModuleList($list, $action = 'access', $use_value = false)
    {
        $result = array();
        if(empty($list)) {
            return $result;
        }
        foreach($list as $key => $module) {
            $checkmodule = $use_value?$module:$key;
            if(!self::checkAccess($checkmodule, $action)) {
                $result[$checkmodule] = $checkmodule;
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
    public static function filterModuleList($list, $action = 'access', $use_value = false)
    {
        $result = array();
        if(empty($list)) {
            return $list;
        }
        foreach($list as $key => $module) {
            if(self::checkAccess($use_value?$module:$key, $action)) {
                $result[$key] = $module;
            }
        }
        return $result;
    }

    /**
     * Filter list of fields and remove/blank fields that we can not access.
     * Modifies the list directly.
     * @param string $module
     * @param array $list list of fields, keys are field names
     * @param array $context
     * @param array options Filtering options:
     * - blank_value (bool) - instead of removing inaccessible field put '' there
     * - add_acl (bool) - instead of removing fields add 'acl' value with access level
     * - suffix (string) - strip suffix from field names
     * - min_access (int) - require this level of access for field
     * - use_value (bool) - look for field name in value, not in key of the list
     */
    public static function listFilter($module, &$list, $context = array(), $options = array())
    {
        if(empty($list)) {
            return;
        }

        if(empty($options['min_access'])) {
            $min_access = 'access';
        } else {
            if($options['min_access'] >= SugarACL::ACL_READ_WRITE) {
                $min_access = "edit";
            }
        }

        $check_fields = array();

        foreach($list as $key=>$value) {
            if(!empty($options['use_value'])) {
                if(is_array($value)) {
                    if(!empty($value['group'])){
                        $value = $value['group'];
                    } elseif(!empty($value['name'])) {
                        $value = $value['name'];
                    } else {
                        // we don't know what to do with this one, skip it
                        continue;
                    }
                }
                $field = $value;
            } else {
                $field = $key;
                if(is_array($value) && !empty($value['group'])){
                        $field = $value['group'];
                }
            }
            if(!empty($options['suffix'])) {
                // remove suffix like _advanced
                $field = str_replace($options['suffix'], '', $field);
            }
            if(!empty($options['add_acl'])) {
                $check_fields[$key] = $field;
            } else {
                if(!empty($list[$key])) {
                    $check_fields[$key] = $field;
                }
            }
        }

        if(empty(self::$acls) || !self::$acls[$module]) {
            self::loadACLs($module, $context);
        }

        if(!empty($options['add_acl'])) {
            // initialize the access details
            foreach($check_fields as $key => $value) {
                $list[$key]['acl'] = self::ACL_READ_WRITE;
            }
            foreach(self::$acls[$module] as $acl) {
                foreach($acl->getFieldListAccess($module, $check_fields, $context) as $key => $acl) {
                    if($acl < $list[$key]['acl']) {
                        $list[$key]['acl'] = $acl;
                    }
                }
            }
        } else {
            foreach(self::$acls[$module] as $acl) {
                foreach($acl->checkFieldList($module, $check_fields, $min_access, $context) as $key => $access) {
                    if(!$access) {
                        // if have no access, blank or remove field value
                        if(empty($options['blank_value'])) {
                        	unset($list[$key]);
                        } else {
                        	$list[$key] = '';
                        }
                        // no need to check it again
                        unset($check_fields[$key]);
                    }
                }
                if(empty($check_fields)) break;
            }
        }
    }
}
