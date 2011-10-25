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


require_once('modules/Employees/Employee.php');
require_once('modules/Users/views/view.list.php');

class Bug46473Test extends Sugar_PHPUnit_Framework_OutputTestCase
{
    public function setUp()
    {
        $this->markTestIncomplete('This test will fail when the entire suite is run.  Probably needs mock objects for the list view objects');
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser(true, 1);
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['mod_strings'] = return_module_language($GLOBALS['current_language'], 'Users');
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        $GLOBALS['action'] = 'index';
        $GLOBALS['module'] = 'Users';
        $_REQUEST['module'] = 'Users';
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['action']);
        unset($GLOBALS['module']);
        unset($_REQUEST['module']);
    }

    public function testUserListView()
    {
        // new employee
        $last_name = 'Test_46473_'.time();
        $emp = new Employee();
        $emp->last_name = $last_name;
        $emp->default_team = 1;
        $emp->status = 'Active';
        $emp->employee_status = 'Active';
        $emp->user_name = 'test_user_name';
        $emp->save();
        $emp_id = $emp->id;
        $this->assertNotNull($emp_id, 'User id should not be null.');

        // list view
        $view = new UsersViewList();
        $view->module = 'Users';
        $view->init($emp);
        $view->lv = new ListViewSmarty();
        $view->display();

        // ensure the new employee shows up in the users list view
        $this->expectOutputRegex('/.*'.$last_name.'.*/');
    }
}

?>