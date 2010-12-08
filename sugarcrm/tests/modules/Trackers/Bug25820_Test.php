<?php
//FILE SUGARCRM flav=pro ONLY
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