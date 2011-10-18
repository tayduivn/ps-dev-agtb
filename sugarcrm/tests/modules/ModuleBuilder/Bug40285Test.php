<?php
/*********************************************************************************
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
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once 'SugarTestUserUtilities.php';
require_once("modules/ModuleBuilder/controller.php");

class Bug40285Test extends Sugar_PHPUnit_Framework_TestCase
{
	
	public function setUp() 
    {
        echo "1\n";
        $_REQUEST [ 'view_module' ] =  'Accounts';
        $_REQUEST [ 'label' ] = 'LBL_REMOVEME';
        $_REQUEST [ 'labelValue' ] = 'removeme';
        $GLOBALS [ 'current_language' ] = 'en_us';
    }

    public function tearDown() 
    {
    }

    /**
     * @group bug40285
     */
    public function testLabelRemoval()
    {
        unset($_REQUEST [ 'view_package' ]);

        $controller = new ModuleBuilderController();

        $controller->action_SaveLabel();

        $lang_file = 'custom/modules/Accounts/language/en_us.lang.php';
        $this->assertFileExists($lang_file);

        unset($mod_strings);
        include($lang_file);

        $this->assertEquals('removeme', $mod_strings['LBL_REMOVEME']);

        $controller->DeleteLabel('en_us', 'LBL_REMOVEME', 'removeme', 'Accounts');

        unset($mod_strings);
        include($lang_file);

        $val = isset($mod_strings['LBL_REMOVEME'])? true: false;

        $this->assertFalse($val);
    }

}