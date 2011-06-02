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
 
class SugarTestLangPackCreatorTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        SugarCache::$isCacheReset = false;

        if( empty($GLOBALS['current_language']) )
            $GLOBALS['current_language'] = $GLOBALS['sugar_config']['default_language'];
    }
    
    public function testSetAnyLanguageStrings() 
    {
        $langpack = new SugarTestLangPackCreator();
        
        $langpack->setAppString('NTC_WELCOME','stringname');
        $langpack->setAppListString('checkbox_dom',array(''=>'','1'=>'Yep','2'=>'Nada'));
        $langpack->setModString('LBL_MODULE_NAME','stringname','Contacts');
        $langpack->save();
        
        $app_strings = return_application_language($GLOBALS['current_language']);
        $app_list_strings = return_app_list_strings_language($GLOBALS['current_language']);
        $mod_strings = return_module_language($GLOBALS['current_language'], 'Contacts');
        
        $this->assertEquals($app_strings['NTC_WELCOME'],'stringname');
        
        $this->assertEquals($app_list_strings['checkbox_dom'],
            array(''=>'','1'=>'Yep','2'=>'Nada'));
        
        $this->assertEquals($mod_strings['LBL_MODULE_NAME'],'stringname');
    }
    
    public function testUndoStringsChangesMade()
    {
        $langpack = new SugarTestLangPackCreator();
        
        $app_strings = return_application_language($GLOBALS['current_language']);
        $prevString = $app_strings['NTC_WELCOME'];
        
        $langpack->setAppString('NTC_WELCOME','stringname');
        $langpack->save();
        
        $app_strings = return_application_language($GLOBALS['current_language']);
        
        $this->assertEquals($app_strings['NTC_WELCOME'],'stringname');
        
        // call the destructor directly to undo our changes
        unset($langpack);
        
        $app_strings = return_application_language($GLOBALS['current_language']);
        
        $this->assertEquals($app_strings['NTC_WELCOME'],$prevString);
    }
}
