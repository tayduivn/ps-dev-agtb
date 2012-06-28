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
 * Base class for ACL implementations
 * @api
 */
abstract class SugarACLStrategy
{
    /**
     * Check access
     * @param string $module
     * @param string $view
     * @param array $context
     * @return bool has access?
     */
    abstract public function checkAccess($module, $view, $context);

    /**
     * Get current user from context
     * @param array $context
     * @return User|null Current user
     */
    public function getCurrentUser($context)
    {
        if(isset($context['user'])) {
            return $context['user'];
        }
        return isset($GLOBALS['current_user'])?$GLOBALS['current_user']:null;
    }

    /**
     * Get current user ID from context
     * @param array $context
     * @return string|null Current user ID
     */
    public function getUserID($context)
    {
        if(isset($context['user'])) {
            return $context['user']->id;
        }
        if(isset($context['user_id'])) {
            return $context['user_id'];
        }
        if(isset($GLOBALS['current_user'])) {
            return $GLOBALS['current_user']->id;
        }
        return null;
    }

    /**
     * Check access for the list of fields
     * @param string $module
     * @param array $field_list key=>value list of fields
     * @param string $action Action to check
     * @param array $context
     * @return array[boolean] Access for each field, array() means all allowed
     */
    public function checkFieldList($module, $field_list, $action, $context)
    {
        $result = array();
        foreach($field_list as $key => $field) {
            $result[$key] = $this->checkAccess($module, "field", $context + array("field" => $field, "action" => $action));
        }
        return $result;
    }

    /**
     * Get access for the list of fields
     * @param string $module
     * @param array $field_list key=>value list of fields
     * @param array $context
     * @return array[int] Access for each field, array() means all allowed
     */
    public function getFieldListAccess($module, $field_list, $context)
    {
        $result = array();
        foreach($field_list as $key => $field) {
            if($this->checkAccess($module, "field", $context + array("field" => $field, "action" => "edit"))) {
                $result[$key] = SugarACL::ACL_READ_WRITE;
            } else if($this->checkAccess($module, "field", $context + array("field" => $field, "action" => "detail"))) {
                $result[$key] = SugarACL::ACL_READ_WRITE;
            } else {
                $result[$key] = SugarACL::ACL_NO_ACCESS;
            }
        }
        return $result;
    }
}