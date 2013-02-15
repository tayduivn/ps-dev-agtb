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



require_once ('include/api/RestService.php');
require_once ("clients/base/api/PersonUnifiedSearchApi.php");


/**
 * @group ApiTests
 */
class PersonUnifiedSearchApiTest extends Sugar_PHPUnit_Framework_TestCase {

    public $personUnifiedSearchApi;

    public function setUp() {
        SugarTestHelper::setUp("current_user");        
        $this->personUnifiedSearchApi = new PersonUnifiedSearchApi();
    }

    public function tearDown() {
        SugarTestHelper::tearDown();
        parent::tearDown();        
    }

    // @Bug 61073
    public function testNoPortalUserReturned() {
        $GLOBALS['current_user']->portal_only = 1;
        $GLOBALS['current_user']->save();
        $args = array('module_list' => 'Users',);
        $list = $this->personUnifiedSearchApi->globalSearch(new PersonUnifiedSearchApiServiceMockUp(), $args);
        $list = $list['records'];
        foreach($list AS $record) {
            $expected[] = $record['id'];
        }

        $this->assertTrue(!in_array($GLOBALS['current_user']->id, $expected));

    }

    public function testNoShowOnEmployees() {
        $GLOBALS['current_user']->show_on_employees = 0;
        $GLOBALS['current_user']->employee_status = 'Active';
        $GLOBALS['current_user']->save();
        $args = array('module_list' => 'Employees',);
        $list = $this->personUnifiedSearchApi->globalSearch(new PersonUnifiedSearchApiServiceMockUp(), $args);
        $list = $list['records'];
        foreach($list AS $record) {
            $expected[] = $record['id'];
        }

        $this->assertTrue(!in_array($GLOBALS['current_user']->id, $expected));
    }

    public function testShowOnEmployees() {
        $GLOBALS['current_user']->show_on_employees = 1;
        $GLOBALS['current_user']->employee_status = 'Active';
        $GLOBALS['current_user']->save();
        $args = array('module_list' => 'Employees',);
        $list = $this->personUnifiedSearchApi->globalSearch(new PersonUnifiedSearchApiServiceMockUp(), $args);
        $list = $list['records'];
        foreach($list AS $record) {
            $expected[] = $record['id'];
        }

        $this->assertTrue(in_array($GLOBALS['current_user']->id, $expected));
    }


}

class PersonUnifiedSearchApiServiceMockUp extends RestService
{
    public function execute() {}
    protected function handleException(Exception $exception) {}
}
