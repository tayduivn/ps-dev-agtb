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

require_once ("include/SugarFields/Fields/Datetimecombo/SugarFieldDatetimecombo.php");


/**
 * @group Bug49691
 */
class Bug49691aTest extends Sugar_PHPUnit_Framework_TestCase {

    var $bean;
    var $sugarField;

    public function setUp() {
        $this->bean = new Bug49691aMockBean();
        $this->sugarField = new SugarFieldDatetimecombo("Accounts");
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
    }

    public function tearDown() {
        unset($GLOBALS['current_user']);
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($this->sugarField);
    }

    /**
     * @dataProvider providerFunction
     */
    public function testDBDateConversion($dateValue, $expected) {
        global $current_user;

        $this->bean->test_c = $dateValue;

        $inputData = array('test_c'=>$dateValue);
        $field = 'test_c';
        $def = '';
        $prefix = '';

        $this->sugarField->save($this->bean, $inputData, $field, $def, $prefix);
        $this->assertNotEmpty($this->bean->test_c);
        $this->assertSame($expected, $this->bean->test_c);
    }

    public function providerFunction() {
        return array(
            array('01/01/2012 12:00', '2012-01-01 12:00:00'),
            array('2012-01-01 12:00:00', '2012-01-01 12:00:00'),
            array('01/01/2012', '2012-01-01'),
            array('2012-01-01', '2012-01-01'),
        );
    }
}

class Bug49691aMockBean {
    var $test_c;
}
