<?php
//FILE SUGARCRM flav=pro ONLY
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
 
require_once('tests/modules/Trackers/TrackerTestUtility.php');
require_once('tests/SugarTestLangPackCreator.php');

class Bug25820_Test extends Sugar_PHPUnit_Framework_TestCase 
{
    private $_trackerReporter;
    
    public function setUp()
    {
        $this->_trackerReporter = new TrackerReporterBug25820Mock();
    }
    
    public function testGetTranslatedModuleNameInModuleList() 
    {
        $langpack = new SugarTestLangPackCreator();
        $langpack->setAppListString('moduleList',array('Contacts'=>'cat'));
        $langpack->save();
        $this->assertEquals(
            $this->_trackerReporter->getGetTranslatedModuleName('Contacts'),
            'cat'
            );
    }
    
    public function testGetTranslatedModuleNameInModStrings() 
    {
        $langpack = new SugarTestLangPackCreator();
        $langpack->setModString('LBL_MODULE_NAME','stringname','Administration');
        $langpack->save();
        
        $this->assertEquals(
            $this->_trackerReporter->getGetTranslatedModuleName('Administration'),
            'stringname'
            );
    }
    
    public function testGetTranslatedModuleNameModuleBuilder() 
    {
        $langpack = new SugarTestLangPackCreator();
        $langpack->setModString('LBL_MODULEBUILDER','stringname','ModuleBuilder');
        $langpack->save();
        
        $this->assertEquals(
            $this->_trackerReporter->getGetTranslatedModuleName('ModuleBuilder'),
            'stringname'
            );
    }
}

require_once('modules/Trackers/TrackerReporter.php');

class TrackerReporterBug25820Mock extends TrackerReporter
{
    public function getGetTranslatedModuleName(
        $moduleName
        )
    {
        return $this->_getTranslatedModuleName($moduleName);
    }
}
?>