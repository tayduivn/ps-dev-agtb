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
 
require_once 'include/utils.php';

class Bug41003Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function providerVerifyStrippingOfBrInBr2nlFunction()
    {
        return array(
            array("here is my text with no newline", "here is my text with no newline"),
            array("here is my text with a newline lowercased\n", "here is my text with a newline lowercased<br>"),
            array("here is my text with a newline mixed case\n", "here is my text with a newline mixed case<Br>"),
            array("here is my text with a newline mixed case with /\n", "here is my text with a newline mixed case with /<Br />"),
            array("here is my text with a newline uppercase\n", "here is my text with a newline uppercase<BR />"),
            array("here is my crappy text éèçàô$*%ù§!#with a newline\n in the middle", "here is my crappy text éèçàô$*%ù§!#with a newline<bR> in the middle"),
            );
    }
    
    /**
     * @dataProvider providerVerifyStrippingOfBrInBr2nlFunction
     */
    public function testVerifyStrippingOfBrInBr2nlFunction($expectedResult,$testString)
    {
        $this->assertEquals($expectedResult, br2nl($testString));
    }
}

