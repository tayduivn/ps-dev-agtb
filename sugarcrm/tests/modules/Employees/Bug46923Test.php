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


require_once('modules/Users/User.php');
require_once('modules/Employees/views/view.list.php');

class Bug46923Test extends Sugar_PHPUnit_Framework_OutputTestCase
{
    public function setUp()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser(true, 1);
        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    public function testUserListView()
    {
        // new employee
        $last_name = 'Test_46923_'.time();
        $user = new User();
        $user->last_name = $last_name;
        $user->default_team = 1;
        $user->status = 'Active';
        $user->employee_status = 'Active';
        $user->user_name = 'test_user_name';
        $user->save();
        $user_id = $user->id;
        $this->assertNotNull($user_id, 'User id should not be null.');

        // list view
        $view = new EmployeesViewList();
        $GLOBALS['action'] = 'index';
        $GLOBALS['module'] = 'Employees';
        $_REQUEST['module'] = 'Employees';
        $view->init($user);
        $view->lv = new ListViewSmarty();
        $view->display();

        // ensure the new user shows up in the employees list view
        $this->expectOutputRegex('/.*'.$last_name.'.*/');

        // cleanup
        unset($GLOBALS['action']);
        unset($GLOBALS['module']);
        unset($_REQUEST['module']);
        $GLOBALS['db']->query("delete from users where id='{$user_id}'");
    }
}

?>