<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
require_once 'modules/Users/User.php';

class UserManagerFunctionsTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $employee1;
    private $employee2;
    private $employee3;
    private $employee4;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        global $current_user;
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        $current_user->user_name = 'employee0';
        $current_user->save();

        $this->employee1 = SugarTestUserUtilities::createAnonymousUser();
        $this->employee1->reports_to_id = $current_user->id;
        $this->employee1->user_name = 'employee1';
        $this->employee1->save();

        $this->employee2 = SugarTestUserUtilities::createAnonymousUser();
        $this->employee2->reports_to_id = $current_user->id;
        $this->employee2->user_name = 'employee2';
        $this->employee2->save();

        $this->employee3 = SugarTestUserUtilities::createAnonymousUser();
        $this->employee3->reports_to_id = $this->employee2->id;
        $this->employee3->user_name = 'employee3';
        $this->employee3->save();

        $this->employee4 = SugarTestUserUtilities::createAnonymousUser();
        $this->employee4->reports_to_id = $this->employee3->id;
        $this->employee4->deleted = 1;
        $this->employee4->user_name = 'employee4';
        $this->employee4->save();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
    }

    public function testUserManagementFunctions()
    {
        global $current_user;
        $this->assertTrue(User::isTopLevelManager($current_user->id), 'current_user is top level manager');
        $this->assertFalse(User::isManager($this->employee1->id), 'employee1 does not report to anyone');
        $this->assertFalse(User::isTopLevelManager($this->employee3->id), 'employee3 is not a top level manager');
        $this->assertFalse(User::isManager($this->employee3->id), 'employee3 is not a manager if we exclude deleted users');
        $this->assertTrue(User::isManager($this->employee3->id, true), 'employee3 is a manager if we include deleted users');
    }
}
