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
		$this->langpack = new SugarTestLangPackCreator();
    }
    
    public function testGetTranslatedModuleNameInModuleList() 
    {
        
        $this->langpack->setAppListString('moduleList',array('Contacts'=>'cat'));
        $this->langpack->save();
        $this->assertEquals(
            $this->_trackerReporter->getGetTranslatedModuleName('Contacts'),
            'cat'
            );
    }
    
    public function testGetTranslatedModuleNameInModStrings() 
    {
        $this->langpack->setModString('LBL_MODULE_NAME','stringname','Administration');
        $this->langpack->save();
        
        $this->assertEquals(
            $this->_trackerReporter->getGetTranslatedModuleName('Administration'),
            'stringname'
            );
    }
    
    public function testGetTranslatedModuleNameModuleBuilder() 
    {
        $this->langpack->setModString('LBL_MODULEBUILDER','stringname','ModuleBuilder');
        $this->langpack->save();
        
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