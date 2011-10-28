<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


//BEGIN SUGARCRM flav=pro ONLY
require_once('modules/Reports/views/view.buildreportmoduletree.php');
require_once('modules/Reports//SavedReport.php');
//END SUGARCRM flav=pro ONLY

class Bug47271Test extends Sugar_PHPUnit_Framework_OutputTestCase
{
    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function testUserListView()
    {
        global $app_list_strings;
        $new_name = 'PeopleXYZ';

        // simulate module renaming
        $org_name = $app_list_strings['moduleList']['Contacts'];
        $app_list_strings['moduleList']['Contacts'] = $new_name;

        // request settings
        $_REQUEST['action'] = 'BuildReportModuleTree';
        $_REQUEST['module'] = 'Reports';
        $_REQUEST['page'] = 'Report';
        $_REQUEST['report_module'] = 'Accounts';

//BEGIN SUGARCRM flav=pro ONLY
        // module tree
        $view = new ReportsViewBuildreportmoduletree();
        $view->display();

        // ensure the module tree includes the new module name
        $pattern = '"text":"' . $new_name . '"'; //  the json string should include: "text":"PeopleXYZ"
        $this->expectOutputRegex('/.*'.$pattern.'.*/');
//END SUGARCRM flav=pro ONLY

        // cleanup
        unset($_REQUEST['report_module']);
        unset($_REQUEST['page']);
        unset($_REQUEST['module']);
        unset($GLOBALS['module']);
        unset($_REQUEST['action']);
        $app_list_strings['moduleList']['Contacts'] = $org_name;
    }
}
?>