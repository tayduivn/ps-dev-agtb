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

require_once 'modules/ModuleBuilder/parsers/views/SidecarGridLayoutMetaDataParser.php';

/**
 * Bug 58038 - KDocuments Portal Detail view edit does not save properly when 
 * using filler cells
 */
class Bug58038Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_module = 'KBOLDDocuments';
    
    public function setUp()
    {
        $this->markTestIncomplete('Need to rewrite the test to handle labels correctly.');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('app_list_strings');
    }
    
    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @group Bug58038
     */
    public function testFillerCellsRemainIntactOnRecordLayoutSave()
    {
        // Get the bean so we can get the field defs
        $kbdocs = BeanFactory::getBean($this->_module);
        
        // Build the test panels array, mocking _populateFromRequest()
        $testPanels = array(
            'LBL_PANEL_DEFAULT' => array(
                array('name', '(empty)'),
                array('active_date', '(empty)'),
                array('date_modified', '(empty)'),
                array('description', '(filler)'),
                array('attachment_list', '(filler)'),
                array('body', '(empty)'),
            ),
        );
        
        // Build the parser
        $parser = new Bug58038LayoutMetaDataParser(MB_PORTALRECORDVIEW, $this->_module, null, MB_PORTAL);
        
        // Get the test result data
        $panels = $parser->getLayoutArrayForTesting($testPanels, $kbdocs->field_defs);
        $fields = $panels[0]['fields'];
        
        
        // Assertions
        $this->assertArrayNotHasKey('displayParams', $fields[3], "Did not expect a displayParams colspan for description but one was found in the result");
        $this->assertEmpty($fields[4], "Filler cell for the fourth row is missing");
        $this->assertEmpty($fields[6], "Filler cell for the sixth row is missing");
        $this->assertArrayHasKey('displayParams', $fields[7], "The last row field - body - should have a displayParam value");
        $this->assertArrayHasKey('colspan', $fields[7]['displayParams'], "The last row field - body - should have a displayParam colspan value");
        $this->assertEquals(2, $fields[7]['displayParams']['colspan'], "Colspan should be 2 for the body field");
    }
}

class Bug58038LayoutMetaDataParser extends SidecarGridLayoutMetaDataParser
{
    public function getLayoutArrayForTesting($panels, $fielddefs)
    {
        return $this->_convertToCanonicalForm($panels, $fielddefs);
    }
}
