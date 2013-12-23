<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
require_once('data/SugarACLStrategy.php');

/**
 * This class is used to enforce ACLs on modules that are restricted to developer or admins only.
 */
class SugarACLDeveloperOrAdmin extends SugarACLStrategy
{
    protected $allowUserRead = false;
    protected $aclModule = '';

    public function __construct($aclOptions)
    {
        if (is_array($aclOptions)) {
            if (!empty($aclOptions['allowUserRead'])) {
                $this->allowUserRead = true;
            }
            if (!empty($aclOptions['aclModule'])) {
                $this->aclModule = $aclOptions['aclModule'];
            }
        }
    }

    /**
     * Only allow access to users with the user developer or admin setting
     * @param string $module
     * @param string $view
     * @param array $context
     * @return bool|void
     */
    public function checkAccess($module, $view, $context)
    {
        if ($view == 'team_security') {
            // Let the other modules decide
            return true;
        }

        if (!empty($this->aclModule)) {
            $module = $this->aclModule;
        }

        $currentUser = $this->getCurrentUser($context);

        if (!$currentUser) {
            return false;
        }

        if($currentUser->isAdminForModule($module) || $currentUser->isDeveloperForModule($module)) {
            return true;
        } 
        else {
            if ($this->allowUserRead && !$this->isWriteOperation($view, $context)) {
                return true;
            } 
            else {
                return false;
            }
        }
    }
}
