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
require_once 'include/Dashlets/Dashlet.php';

class DashletLoadLanguageTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_moduleName;
    
    public function setup()
    {
        $GLOBALS['dashletStrings'] = array();
        $this->_moduleName = 'TestModuleForDashletLoadLanguageTest'.mt_rand();
    }
    
    public function tearDown()
    {
        if ( is_dir("modules/{$this->_moduleName}") )
            rmdir_recursive("modules/{$this->_moduleName}");
        if ( is_dir("custom/modules/{$this->_moduleName}") )
            rmdir_recursive("custom/modules/{$this->_moduleName}");
        
        unset($GLOBALS['dashletStrings']);
        $GLOBALS['current_language'] = $GLOBALS['sugar_config']['default_language'];
    }
    
    public function testCanLoadCurrentLanguageAppStrings() 
    {
        $GLOBALS['current_language'] = 'en_us';
        sugar_mkdir("modules/{$this->_moduleName}/Dashlets/TestModuleDashlet/",null,true);
        sugar_file_put_contents("modules/{$this->_moduleName}/Dashlets/TestModuleDashlet/TestModuleDashlet.en_us.lang.php",
            '<?php $dashletStrings["TestModuleDashlet"]["foo"] = "bar"; ?>');
        
        $dashlet = new Dashlet(0);
        $dashlet->loadLanguage('TestModuleDashlet',"modules/{$this->_moduleName}/Dashlets/");
        
        $this->assertEquals("bar",$dashlet->dashletStrings["foo"]);
    }
    
    public function testCanLoadCustomLanguageAppStrings() 
    {
        $GLOBALS['current_language'] = 'en_us';
        sugar_mkdir("modules/{$this->_moduleName}/Dashlets/TestModuleDashlet/",null,true);
        sugar_file_put_contents("modules/{$this->_moduleName}/Dashlets/TestModuleDashlet/TestModuleDashlet.en_us.lang.php",
            '<?php $dashletStrings["TestModuleDashlet"]["foo"] = "bar"; ?>');
        create_custom_directory("modules/{$this->_moduleName}/Dashlets/TestModuleDashlet/");
        sugar_file_put_contents("custom/modules/{$this->_moduleName}/Dashlets/TestModuleDashlet/TestModuleDashlet.en_us.lang.php",
            '<?php $dashletStrings["TestModuleDashlet"]["foo"] = "barbar"; ?>');
        
        $dashlet = new Dashlet(0);
        $dashlet->loadLanguage('TestModuleDashlet',"modules/{$this->_moduleName}/Dashlets/");
        
        $this->assertEquals("barbar",$dashlet->dashletStrings["foo"]);
    }
    
    public function testCanLoadCustomLanguageAppStringsWhenThereIsNoNoncustomLanguageFile() 
    {
        $GLOBALS['current_language'] = 'en_us';
        create_custom_directory("modules/{$this->_moduleName}/Dashlets/TestModuleDashlet/");
        sugar_file_put_contents("custom/modules/{$this->_moduleName}/Dashlets/TestModuleDashlet/TestModuleDashlet.en_us.lang.php",
            '<?php $dashletStrings["TestModuleDashlet"]["foo"] = "barbar"; ?>');
        
        $dashlet = new Dashlet(0);
        $dashlet->loadLanguage('TestModuleDashlet',"modules/{$this->_moduleName}/Dashlets/");
        
        $this->assertEquals("barbar",$dashlet->dashletStrings["foo"]);
    }
    
    public function testCanLoadCurrentLanguageAppStringsWhenNotEnglish() 
    {
        $GLOBALS['current_language'] = 'FR_fr';
        sugar_mkdir("modules/{$this->_moduleName}/Dashlets/TestModuleDashlet/",null,true);
        sugar_file_put_contents("modules/{$this->_moduleName}/Dashlets/TestModuleDashlet/TestModuleDashlet.en_us.lang.php",
            '<?php $dashletStrings["TestModuleDashlet"]["foo"] = "bar"; ?>');
        sugar_mkdir("modules/{$this->_moduleName}/Dashlets/TestModuleDashlet/",null,true);
        sugar_file_put_contents("modules/{$this->_moduleName}/Dashlets/TestModuleDashlet/TestModuleDashlet.FR_fr.lang.php",
            '<?php $dashletStrings["TestModuleDashlet"]["foo"] = "barrie"; ?>');
        
        $dashlet = new Dashlet(0);
        $dashlet->loadLanguage('TestModuleDashlet',"modules/{$this->_moduleName}/Dashlets/");
        
        $this->assertEquals("barrie",$dashlet->dashletStrings["foo"]);
    }
    
    public function testCanLoadEnglishLanguageAppStringsWhenCurrentLanguageDoesNotExist() 
    {
        $GLOBALS['current_language'] = 'FR_fr';
        sugar_mkdir("modules/{$this->_moduleName}/Dashlets/TestModuleDashlet/",null,true);
        sugar_file_put_contents("modules/{$this->_moduleName}/Dashlets/TestModuleDashlet/TestModuleDashlet.en_us.lang.php",
            '<?php $dashletStrings["TestModuleDashlet"]["foo"] = "bar"; ?>');
        
        $dashlet = new Dashlet(0);
        $dashlet->loadLanguage('TestModuleDashlet',"modules/{$this->_moduleName}/Dashlets/");
        
        $this->assertEquals("bar",$dashlet->dashletStrings["foo"]);
    }
    
    public function testCanLoadCustomEnglishLanguageAppStringsWhenCurrentLanguageDoesNotExist() 
    {
        $GLOBALS['current_language'] = 'FR_fr';
        sugar_mkdir("modules/{$this->_moduleName}/Dashlets/TestModuleDashlet/",null,true);
        sugar_file_put_contents("modules/{$this->_moduleName}/Dashlets/TestModuleDashlet/TestModuleDashlet.en_us.lang.php",
            '<?php $dashletStrings["TestModuleDashlet"]["foo"] = "bar"; ?>');
        create_custom_directory("modules/{$this->_moduleName}/Dashlets/TestModuleDashlet/");
        sugar_file_put_contents("custom/modules/{$this->_moduleName}/Dashlets/TestModuleDashlet/TestModuleDashlet.en_us.lang.php",
            '<?php $dashletStrings["TestModuleDashlet"]["foo"] = "barbarbar"; ?>');
        
        $dashlet = new Dashlet(0);
        $dashlet->loadLanguage('TestModuleDashlet',"modules/{$this->_moduleName}/Dashlets/");
        
        $this->assertEquals("barbarbar",$dashlet->dashletStrings["foo"]);
    }
}
