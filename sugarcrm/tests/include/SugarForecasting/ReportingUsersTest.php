<?php
// FILE SUGARCRM flav=pro ONLY
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
require_once('include/SugarForecasting/ReportingUsers.php');
class SugarForecasting_ReportingUsersTest extends Sugar_PHPUnit_Framework_TestCase
{

    protected static $users = array();

    /**
     * @var SugarForecasting_ReportingUsers
     */
    protected static $cls;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');

        self::$users['mgr'] = SugarTestUserUtilities::createAnonymousUser();
        self::$users['mgr2'] = SugarTestUserUtilities::createAnonymousUser();
        self::$users['mgr2']->reports_to_id = self::$users['mgr']->id;
        self::$users['mgr2']->save();

        self::$users['rep1'] = SugarTestUserUtilities::createAnonymousUser();
        self::$users['rep1']->reports_to_id = self::$users['mgr2']->id;
        self::$users['rep1']->save();

        self::$users['rep2'] = SugarTestUserUtilities::createAnonymousUser();
        self::$users['rep2']->reports_to_id = self::$users['mgr2']->id;
        self::$users['rep2']->save();

        self::$users['mgr3'] = SugarTestUserUtilities::createAnonymousUser();
        self::$users['mgr3']->reports_to_id = self::$users['mgr']->id;
        self::$users['mgr3']->save();

        self::$users['rep3'] = SugarTestUserUtilities::createAnonymousUser();
        self::$users['rep3']->reports_to_id = self::$users['mgr3']->id;
        self::$users['rep3']->save();

        self::$users['rep4'] = SugarTestUserUtilities::createAnonymousUser();
        self::$users['rep4']->reports_to_id = self::$users['mgr3']->id;
        self::$users['rep4']->save();

        self::$cls = new SugarForecasting_ReportingUsers(array('user_id' => self::$users['mgr']->id));
    }

    public static function tearDownAfterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }
    
    public function testReturnTreeShouldContain3Children()
    {
        $tree = self::$cls->process();

        $this->assertEquals(3, count($tree['children']));

        return $tree;
    }

    /**
     * @group forecasts
     * @depends testReturnTreeShouldContain3Children
     * @param $tree
     */
    public function testFirstChildIsManagerName($tree)
    {
        $this->assertEquals(self::$users['mgr']->full_name, $tree['children'][0]['data']);
    }

    /**
     * @group forecasts
     * @return array|string
     */
    public function testFetchReporteeContainsTwoNodes()
    {
        self::$cls->setArg('user_id', self::$users['mgr2']->id);

        $tree = self::$cls->process();

        $this->assertEquals(2, count($tree));

        return $tree;
    }

    /**
     * @group forecasts
     * @depends testFetchReporteeContainsTwoNodes
     * @param $tree
     */
    public function testReporteeFirstObjectIsParentLink($tree)
    {
        $this->assertEquals('Parent', $tree[0]['data']);
    }

    /**
     * @group forecasts
     * @depends testFetchReporteeContainsTwoNodes
     * @param $tree
     */
    public function testReporteeTreeContainsThreeChildren($tree)
    {
        $this->assertEquals(3, count($tree[1]['children']));
    }

    /**
     * @group forecasts
     * @depends testFetchReporteeContainsTwoNodes
     * @param $tree
     */
    public function testReporteeFirstChildIsManagerName($tree)
    {
        $this->assertEquals(self::$users['mgr2']->full_name, $tree[1]['children'][0]['data']);
    }
}
