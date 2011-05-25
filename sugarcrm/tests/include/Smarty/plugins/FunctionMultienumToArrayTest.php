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
 
require_once('include/Smarty/plugins/function.multienum_to_array.php');
require_once 'include/Sugar_Smarty.php';

class FunctionMultienumToArrayTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->_smarty = new Sugar_Smarty;
    }
    
    public function providerPassedString()
    {
        return array(
            array("Employee^,^Boss","Cold Call",array('Employee','Boss')),
            array("^Employee^,^Boss^","Cold Call",array('Employee','Boss')),
            array("^Employee^","Cold Call",array('Employee')),
            array("Employee","Cold Call",array('Employee')),
            array("","^Cold Call^",array("Cold Call")),
            array(array("Employee"),"Cold Call",array("Employee")),
            array(NULL,array("Employee"),array("Employee")),
            );
    }
    
    /**
     * @ticket 21574
     * @dataProvider providerPassedString
     */
	public function testPassedString(
        $string,
        $default,
        $result
        )
    {
        $params = array();
        $params['string']  = $string;
        $params['default'] = $default;
        
        $this->assertEquals($result, smarty_function_multienum_to_array($params, $this->_smarty));
    }
	
	public function testAssignSmartyVariable()
    {
        $params = array();
        $params['string']  = "^Employee^";
        $params['default'] = "Cold Call";
		$params['assign'] = "multi";
		smarty_function_multienum_to_array($params, $this->_smarty);
        
        $this->assertEquals(
            $this->_smarty->get_template_vars($params['assign']),
            array("Employee")
        );
    }
}