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

/**
 * Tests that translations happen properly and does not modify global mod_strings
 */
class Bug60664Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Holder for the current mod_strings if there are any
     * 
     * @var null|array
     */
    protected static $_modStrings = null;

    public static function setUpBeforeClass()
    {
        // We are working directly on mod_strings, so we won't set it up but rather
        // back it up if necessary
        if (isset($GLOBALS['mod_strings'])) {
            self::$_modStrings = $GLOBALS['mod_strings'];
        }
        
        // Create our own test mod strings for this test
        $GLOBALS['mod_strings'] = array(
            'LBL_TEST1' => 'Test Label',
            'LBL_TEST2' => 'Second Label',
        );
    }
    
    public static function tearDownAfterClass()
    {
        if (self::$_modStrings) {
            $GLOBALS['mod_strings'] = self::$_modStrings;
        }
    }

    /**
     * Tests that a translation occurred properly
     * 
     * @dataProvider labelProvider
     * @group Bug60664
     * @param string $label The label to translate
     * @param string $expects The expected translation
     * @param string $module The module to use for mod_strings fetching
     */
    public function testTranslateDoesNotUseVName($label, $expects, $module)
    {
        $actual = translate($label, $module);
        $this->assertEquals($expects, $actual, "Translated value of $label in $module module was not $expects: $actual");
    }

    /**
     * Tests that the GLOBALS['mod_strings'] did not get manipulated
     * 
     * @group Bug60664
     */
    public function testGlobalModStringsWasNotMutated()
    {
        $this->assertEquals(2, count($GLOBALS['mod_strings']), "Global mod_strings was manipulated");
    }
    
    public function labelProvider()
    {
        return array(
            array('label' => 'LBL_TEST1', 'expects' => 'Test Label', 'module' => '',),
            array('label' => 'LBL_TEST2', 'expects' => 'Second Label', 'module' => '',),
            array('label' => 'LBL_ACCOUNT_INFORMATION', 'expects' => 'Overview', 'module' => 'Accounts',),
            array('label' => 'LBL_CONVERTLEAD_BUTTON_KEY', 'expects' => 'V', 'module' => 'Leads',),
            array('label' => 'LBL_HIDEOPTIONS', 'expects' => 'Hide Options', 'module' => 'ModuleBuilder',),
        );
    }
}