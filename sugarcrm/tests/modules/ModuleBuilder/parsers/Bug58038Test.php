<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once 'modules/ModuleBuilder/parsers/views/SidecarGridLayoutMetaDataParser.php';

/**
 * Bug 58038 - KDocuments Portal Detail view edit does not save properly when 
 * using filler cells
 */
class Bug58038Test extends Sugar_PHPUnit_Framework_TestCase
{
    protected $_module = 'KBDocuments';
    
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
    public function testFillerCellsRemainIntactOnDetailLayoutSave()
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
        $parser = new Bug58038LayoutMetaDataParser(MB_PORTALDETAILVIEW, $this->_module, null, MB_PORTAL);
        
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