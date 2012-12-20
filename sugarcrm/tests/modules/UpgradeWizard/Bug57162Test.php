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

require_once('modules/UpgradeWizard/uw_utils.php');

/**
 * Bug #57162
 * Upgrader needs to handle 3-dots releases and double digit values
 *
 * @author mgusev@sugarcrm.com
 * @ticked 57162
 */
class Bug57162Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function dataProvider()
    {
        return array(
            array('656', array('6.5.6')),
            array('660', array('6.6.0beta1')),
            array('640', array('6.4.0rc2')),
            array('600', array('6', 3)),
            array('6601', array('6.6.0.1')),
            array('6601', array('6.6.0.1', 0)),
            array('660', array('6.6.0.1', 3)),
            array('660', array('6.6.0.1', 3, '')),
            array('66x', array('6.6.0.1', 3, 'x')),
            array('660x', array('6.6.0.1', 0, 'x')),
            array('6.6.x', array('6.6.0.1', 3, 'x', '.')),
            array('6-6-0-beta2', array('6.6.0.1', 0, 'beta2', '-')),
            array('6601', array('6.6.0.1', 0, '', '')),
            array('', array('test342lk')),
            array('650', array('6.5.6' ,0, '0')),
            array('60', array('6.5.6', 2, 0)),
        );
    }

    /**
     * Test asserts result of implodeVersion function
     *
     * @group 57162
     * @dataProvider dataProvider
     * @param string $expect version
     * @param array $params for implodeVersion function
     */
    public function testImplodeVersion($expected, $params)
    {
        $actual = call_user_func_array('implodeVersion', $params);
        $this->assertEquals($expected, $actual, 'Result is incorrect');
    }
}
