<?php
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

// Setup current_user in the global space
$user = new User(); // Admin user
$current_user = $user->getSystemUser(); // Used as a global in the quick create
$current_user->default_team_name = 'Global';

class Bug51105Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function testCheckEditViewHeaderTpl()
    {
        require_once 'include/EditView/SubpanelQuickCreate.php';
        $module = 'Notes';
        $view   = 'QuickCreate';
        $error  = 'Unexpected headerTpl value';
        $sqc1   = new SubpanelQuickCreate($module, $view, true);

        // For comparison, get the module view defs for this module/view
        $moduledefs = $this->getModuleViewDefs($sqc1, $module, $view);

        // Set our default template
        $defaulttpl = 'include/EditView/header.tpl';

        // Get the module template as the SubpanelQuickCreate object SHOULD get it
        $moduletpl  = empty($moduledefs['templateMeta']['form']['headerTpl']) ? $defaulttpl : $moduledefs['templateMeta']['form']['headerTpl'];

        // First run, no request vars for return_module or return_relationship
        $this->assertEquals($moduletpl, $sqc1->ev->defs['templateMeta']['form']['headerTpl'], $error);

        // Now set the request props we expect and run it again
        $_REQUEST['return_module'] = 'Notes';
        $_REQUEST['return_relationship'] = 'leads_notes';
        $sqc2 = new SubpanelQuickCreate($module, $view, true);
        $this->assertEquals($defaulttpl, $sqc2->ev->defs['templateMeta']['form']['headerTpl'], $error);
    }

    public function getModuleViewDefs(SubpanelQuickCreate $sqc, $module, $view)
    {
        $return = array();

        $metafile = $sqc->getModuleViewDefsSourceFile($module, $view);
        if (file_exists($metafile))
        {
            include $metafile;
            if (!empty($viewdefs[$module][$view]))
            {
                $return = $viewdefs[$module][$view];
            }
        }

        return $return;
    }
}