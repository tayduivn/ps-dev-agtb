<?php

/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

/**
 * Bug55154Test.php
 *
 * Tests KBDocuments Module 'keywords' field is not available in any layout.
 * 
 * Using the parser factory delegates including necessary parser files at construct
 * time as opposed to loading all required files per fixture.
 */
require_once('modules/ModuleBuilder/parsers/ParserFactory.php');

class Bug55154Test extends Sugar_PHPUnit_Framework_TestCase {
    protected $testModule;
    protected $testField  = 'keywords';
    protected static $testModuleStatic = 'KBDocuments';
    
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setup('beanList');
        SugarTestHelper::setup('beanFiles');
        SugarTestHelper::setup('app_list_strings');
        SugarTestHelper::setup('mod_strings', array(self::$testModuleStatic));
    }
    
    public static function tearDownAfterClass() 
    {
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }
    
    public function setUp()
    {
        $this->testModule = self::$testModuleStatic;
    }
    
    public function tearDown()
    {
        unset($this->testModule);
        parent::tearDown();
    }
    
    /**
     * Does not test additional fields as OOTB instances do not have an additional
     * fields list. Should that change in the future, add the following:
     * <code>
     * $fields = $parser->getAdditionalFields();
     * $this->assertArrayNotHasKey($this->testField, $fields, "$this->testField should not be in the additional fields list");
     * </code> 
     */
    public function testBaseListView()
    {
        // ListLayoutMetaDataParser
        $parser = ParserFactory::getParser(MB_LISTVIEW, $this->testModule);
        
        // Currently included fields
        $fields = $parser->getDefaultFields();
        $this->assertArrayNotHasKey($this->testField, $fields, "$this->testField should not be in the default fields list");
        
        // Available but not shown fields
        $fields = $parser->getAvailableFields();
        $this->assertArrayNotHasKey($this->testField, $fields, "$this->testField should not be in the available fields list");
    }
    
    public function testPopupListView()
    {
        // PopupMetaDataParser
        $parser = ParserFactory::getParser(MB_POPUPLIST, $this->testModule);
        
        // Currently included fields
        $fields = $parser->getSearchFields();
        $this->assertArrayNotHasKey($this->testField, $fields, "$this->testField should not be in the default popup list fields list");
        
        // Available but not shown fields
        $fields = $parser->getAvailableFields();
        $this->assertArrayNotHasKey($this->testField, $fields, "$this->testField should not be in the available fields list");
        // this is to suppress output. Need to fix properly with a good unit test.
        $this->expectOutputRegex('//');
    }
    
    public function testPopupSearchView()
    {
        // PopupMetaDataParser
        $parser = ParserFactory::getParser(MB_POPUPSEARCH, $this->testModule);
        
        // Currently included fields
        $fields = $parser->getSearchFields();
        $this->assertArrayNotHasKey($this->testField, $fields, "$this->testField should not be in the default popup search fields list");
        
        // Available but not shown fields
        $fields = $parser->getAvailableFields();
        $this->assertArrayNotHasKey($this->testField, $fields, "$this->testField should not be in the available fields list");
    }
    public function testMobileEditView()
    {
        // SidecarGridLayoutMetaDataParser
        $parser = ParserFactory::getParser(MB_WIRELESSEDITVIEW, $this->testModule, null, null, MB_WIRELESS);
        
        // Currently rendered fields
        $fields = $parser->getLayout();
        $test = $this->_fieldNameFoundInFields($this->testField, $fields['LBL_PANEL_DEFAULT']);
        $this->assertFalse($test, "$this->testField should not be in default fields");
        
        // Fields that can be added to a layout
        $fields = $parser->getAvailableFields();
        $test = $this->_fieldNameFoundInFields($this->testField, $fields);
        $this->assertFalse($test, "$this->testField should not be in available fields");
    }
    
    public function testMobileDetailView()
    {
        // SidecarGridLayoutMetaDataParser
        $parser = ParserFactory::getParser(MB_WIRELESSDETAILVIEW, $this->testModule, null, null, MB_WIRELESS);
        
        // Currently rendered fields
        $fields = $parser->getLayout();
        $test = $this->_fieldNameFoundInFields($this->testField, $fields['LBL_PANEL_DEFAULT']);
        $this->assertFalse($test, "$this->testField should not be default fields");
        
        // Fields that can be added to a layout
        $fields = $parser->getAvailableFields();
        $test = $this->_fieldNameFoundInFields($this->testField, $fields);
        $this->assertFalse($test, "$this->testField should not be in available fields");
    }
    
    /**
     * Does not test additional fields as OOTB instances do not have an additional
     * fields list. Should that change in the future, add the following:
     * <code>
     * $fields = $parser->getAdditionalFields();
     * $this->assertArrayNotHasKey($this->testField, $fields, "$this->testField should not be in the additional fields list");
     * </code> 
     */
    public function testMobileListView() 
    {
        // SidecarListLayoutMetaDataParser
        $parser = ParserFactory::getParser(MB_WIRELESSLISTVIEW, $this->testModule, null, null, MB_WIRELESS);
        
        // Currently included fields
        $fields = $parser->getDefaultFields();
        $this->assertArrayNotHasKey($this->testField, $fields, "$this->testField should not be in the default fields list");
        
        // Available but not shown fields
        $fields = $parser->getAvailableFields();
        $this->assertArrayNotHasKey($this->testField, $fields, "$this->testField should not be in the available fields list");
    }
    
    //BEGIN SUGARCRM flav=ent ONLY
    public function testPortalRecordView()
    {
        // SidecarGridLayoutMetaDataParser
        $parser = ParserFactory::getParser(MB_PORTALRECORDVIEW, $this->testModule, null, null, MB_PORTAL);
        
        // Currently rendered fields
        $layout = $parser->getLayout();
        $test = $this->_fieldNameFoundInLayoutFields($this->testField, $layout);
        $this->assertFalse($test, "$this->testField should not be a layout field");
        
        // Fields that can be added to a layout
        $fields = $parser->getAvailableFields();
        $test = $this->_fieldNameFoundInFields($this->testField, $fields);
        $this->assertFalse($test, "$this->testField should not be in available fields");
    }

    /**
     * Does not test additional fields as OOTB instances do not have an additional
     * fields list. Should that change in the future, add the following:
     * <code>
     * $fields = $parser->getAdditionalFields();
     * $this->assertArrayNotHasKey($this->testField, $fields, "$this->testField should not be in the additional fields list");
     * </code> 
     */
    public function testPortalListView()
    {
        // SidecarListLayoutMetaDataParser
        $parser = ParserFactory::getParser(MB_PORTALLISTVIEW, $this->testModule, null, null, MB_PORTAL);
        
        // Currently included fields
        $fields = $parser->getDefaultFields();
        $this->assertArrayNotHasKey($this->testField, $fields, "$this->testField should not be in the default fields list");
        
        // Available but not shown fields
        $fields = $parser->getAvailableFields();
        $this->assertArrayNotHasKey($this->testField, $fields, "$this->testField should not be in the available fields list");
        
        // Hidden fields are not tested since OOTB installs have no hidden fields in the layout
    }
    //END SUGARCRM flav=ent ONLY
    /**
     * Utility method to parse field defs for MOST grid type layouts
     * 
     * @param string $name The field name to check for
     * @param array $fields The defs to search
     * @return bool
     */
    protected function _fieldNameFoundInFields($name, $fields) {
        foreach ($fields as $field) {
            if (isset($field['name']) && $field['name'] == $name) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Utility method to search layout defs for mobile grid layouts for a field
     * 
     * @param string $name The field name to search for
     * @param array $layout The defs to search
     * @return bool
     */
    protected function _fieldNameFoundInLayoutFields($name, $layout) {
        foreach ($layout as $fields) {
            if ($this->_fieldNameFoundInFields($name, $fields)) {
                return true;
            }
        }
        
        return false;
    }
}
