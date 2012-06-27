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
 
require_once 'include/Smarty/plugins/function.sugar_help.php';
require_once 'include/Sugar_Smarty.php';

class FunctionSugarHelpTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarTestHelper::setUp('app_strings');
        $this->_smarty = new Sugar_Smarty;
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    public function providerSpecialCharactersHandledInTextParameter()
    {
        return array(
            array(
                'dog "the" bounty hunter & friends are <b>cool</b>',
                'dog &quot;the&quot; bounty hunter &amp; friends are &lt;b&gt;cool&lt;/b&gt;',
                ),
            array(
                "dog 'the' bounty hunter",
                "dog \'the\' bounty hunter"
                ),
            );
    }
    
    /**
     * @dataProvider providerSpecialCharactersHandledInTextParameter
     */
	public function testSpecialCharactersHandledInTextParameter(
        $string,
        $returnedString
        )
    {
        $this->assertContains($returnedString, smarty_function_sugar_help(array('text'=>$string),$this->_smarty));
    }
    
    public function testExtraParametersAreAdded()
    {
        $string = 'my string';
        
        $output = smarty_function_sugar_help(array('text'=>$string,'myPos'=>'foo', 'atPos'=>'bar'),$this->_smarty);
        
        $this->assertContains(",'foo','bar'",$output);
    }
}
