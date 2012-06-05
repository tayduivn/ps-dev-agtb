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
class SugarACLSupportPortal extends SugarACLStrategy
{
    /**
     * Is the current user a portal user?
     * @return bool Yes, the user is a portal user
     */
    public function isPortalUser()
    {
        if (!empty($_SESSION['type']) && $_SESSION['type'] == 'support_portal' ) {
            return true;
        }
        return false;
    }

    /**
     * (non-PHPdoc)
     * @see SugarACLStrategy::checkAccess()
     */
    public function checkAccess($module, $action, $context)
    {
        if ( !$this->isPortalUser() ) {
            // Not a portal user, always return true
            // true sends the system on to check further ACL's
            return true;
        }

        //BEGIN SUGARCRM flav=pro ONLY
        // Team security is always disabled for portal users
        if($action == "team_security") {
            return false;
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

        // Not a field, not a bean, return true and let the normal ACL handle it.
        return true;
    }

    static $action_translate = array(
        'listview' => 'list',
        'index' => 'list',
        'popupeditview' => 'edit',
        'editview' => 'edit',
        'detail' => 'view',
        'detailview' => 'view',
        'save' => 'edit',
        'create' => 'edit',
    );

    /**
     * Check access to fields
     * @param string $module
     * @param string $action
     * @param array $context
     */
    protected function fieldACL($module, $action, $context)
    {
        if (!$this->isPortalUser() ) {
            return true;
        }

        $bean = isset($context['bean'])?$context['bean']:null;
        if (!$bean) {
            // There is no bean, without a bean portal ACL's wont work
            // So for security we will deny the request
            return false;
        }

        $is_owner = false;
        if(!empty($context['owner_override'])) {
            $is_owner = $context['owner_override'];
        } else {
            if($bean) {
                $is_owner = $bean->isOwner();
            }
        }

        if(isset(self::$action_translate[$action])) {
            $action = self::$action_translate[$action];
        }

        // Only allow users to create records, never edit
        if ($action=='edit' || $action=='create' || $action=='save') {
            if (empty($bean->id) || $bean->new_with_id ) {
                return true;
            } else {
                return false;
            }
        } else {
            // If they aren't writing to the field, let the normal ACL's take control
            return true;
        }
    }

    /**
     * Check bean ACLs
     * @param string $module
     * @param string $action
     * @param array $context
     */
    protected function beanACL($module, $action, $context)
    {
        if (!$this->isPortalUser() ) {
            return true;
        }

        $bean = $context['bean'];

        if(!empty($context['owner_override'])) {
            $is_owner = $context['owner_override'];
        } else {
            $is_owner = $bean->isOwner();
        }

        if(isset(self::$action_translate[$action])) {
            $action = self::$action_translate[$action];
        }

        switch ($action)
        {
            case 'edit':
                if (empty($bean->id) || $bean->new_with_id ) {
                    // It's really create
                    return true;
                } else {
                    // They are trying to edit, which isn't allowed
                    return false;
                }
        }
        //if it is not one of the above views then it should be implemented on the page level
        return true;

    }
}
