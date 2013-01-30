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



require_once ('include/api/ServiceBase.php');
require_once ("data/SugarBeanApiHelper.php");


/**
 * @group ApiTests
 */
class SugarBeanApiHelperTest extends Sugar_PHPUnit_Framework_TestCase {

    var $bean;
    var $beanApiHelper;

    var $oldDate;
    var $oldTime;

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        // Mocking out SugarBean to avoid having to deal with any dependencies other than those that we need for this test
        $mock = $this->getMock('SugarBean');
        $mock->expects($this->any())
             ->method('ACLFieldAccess')
             ->will($this->returnValue(true));
        $mock->id = 'SugarBeanApiHelperMockBean-1';
        $mock->favorite = false;
        $mock->field_defs = array(
                'testInt' => array(
                    'type' => 'int',
                ),
                'testDecimal' => array(
                    'type' => 'decimal'
                ),
            );
        $this->bean = $mock;
        $this->beanApiHelper = new SugarBeanApiHelper(new ServiceMockup());
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @dataProvider providerFunction
     */
    public function testFormatForApi($fieldName, $fieldValue, $expectedFormattedValue, $message) {

        $this->bean->$fieldName = $fieldValue;

        $data = $this->beanApiHelper->formatForApi($this->bean);
        $this->assertSame($expectedFormattedValue, $data[$fieldName], $message);
    }

    public function providerFunction() {
        return array(
            array('testInt', '', null, 'Bug 57507 regression: expected formatted value for a null int type to be NULL'),
            array('testDecimal', '', null, 'Bug 59692 regression: expected formatted value for a null decimal type to be NULL'),
            array('testInt', '1', 1, "Int type conversion of '1' failed"),
            array('testDecimal', '1', 1.0, "Decimal type conversion of '1' failed"),
            array('testInt', 1.0, 1, "Int type conversion of 1.0 failed"),
            array('testDecimal', 1, 1.0, "Decimal type conversion of 1 failed"),
            array('testInt', '0', 0, "Int type conversion of '0' failed"),
            array('testDecimal', '0', 0.0, "Decimal type conversion of '0' failed"),
            array('testInt', 0.0, 0, "Int type conversion of 0.0 failed"),
            array('testDecimal', 0, 0.0, "Decimal type conversion of 0 failed"),
        );
    }
}

class ServiceMockup extends ServiceBase
{
    public function execute() {}
    protected function handleException(Exception $exception) {}
}
