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

/**
 * Bug55154Test.php
 *
 * Tests KBDocuments Module 'keywords' field is not available in any layout.
 * 
 * Using the parser factory delegates including necessary parser files at construct
 * time as opposed to loading all required files per fixture.
 */
require_once('modules/ModuleBuilder/parsers/ParserFactory.php');

class Bug56838Test extends Sugar_PHPUnit_Framework_TestCase {
    protected static $testModule = 'Cases';

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setup('beanList');
        SugarTestHelper::setup('beanFiles');
        SugarTestHelper::setup('app_list_strings');
        SugarTestHelper::setup('mod_strings', array(self::$testModule));
    }
    
    public static function tearDownAfterClass() 
    {
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    /**
     * @group Bug56838
     */
    public function testMobileEditViewPanelLabelIsCorrect()
    {
        // SidecarGridLayoutMetaDataParser
        $parser = ParserFactory::getParser(MB_WIRELESSEDITVIEW, self::$testModule, null, null, MB_WIRELESS);
        
        // Current layout
        $layout = $parser->getLayout();
        $this->assertArrayNotHasKey('LBL_PANEL_1', $layout, "Layout still shows LBL_PANEL_1 as the default label on mobile edit views");
        $this->assertArrayHasKey('LBL_PANEL_DEFAULT', $layout, "'LBL_PANEL_DEFAULT' was not found as the default panel label on mobile edit views");
    }
    
    /**
     * @group Bug56838
     */
    public function testMobileDetailViewPanelLabelIsCorrect()
    {
        // SidecarGridLayoutMetaDataParser
        $parser = ParserFactory::getParser(MB_WIRELESSDETAILVIEW, self::$testModule, null, null, MB_WIRELESS);
        
        // Current layout
        $layout = $parser->getLayout();
        $this->assertArrayNotHasKey('LBL_PANEL_1', $layout, "Layout still shows LBL_PANEL_1 as the default label on mobile detail views");
        $this->assertArrayHasKey('LBL_PANEL_DEFAULT', $layout, "'LBL_PANEL_DEFAULT' was not found as the default panel label on mobile detail views");
    }
    
    /**
     * @group Bug56838
     */
    public function testMobileListViewPanelLabelIsCorrect() 
    {
        // SidecarListLayoutMetaDataParser
        $parser = ParserFactory::getParser(MB_WIRELESSLISTVIEW, self::$testModule, null, null, MB_WIRELESS);
        
        // List panel defs
        $paneldefs = $parser->getPanelDefs();
        $this->assertNotEmpty($paneldefs, "Panel defs are empty for mobile list view");
        $this->assertTrue(is_array($paneldefs), "Panel defs for mobile list view are not an array");
        $this->assertTrue(isset($paneldefs[0]['label']), "There is no label for mobile list view defs");
        $this->assertEquals($paneldefs[0]['label'], 'LBL_PANEL_DEFAULT', "Expected mobile list view panel label to be 'LBL_PANEL_DEFAULT' but got '{$paneldefs[0]['label']}'");
    }

    //BEGIN SUGARCRM flav=ent ONLY
    /**
     * @group Bug56838
     */
    public function testPortalRecordViewLabelIsCorrect()
    {
        // SidecarGridLayoutMetaDataParser
        $parser = ParserFactory::getParser(MB_PORTALRECORDVIEW, self::$testModule, null, null, MB_PORTAL);
        
        // Current layout
        $layout = $parser->getLayout();
        $this->assertArrayNotHasKey('LBL_PANEL_1', $layout, "Layout still shows LBL_PANEL_1 as the default label on portal record views");
        $this->assertArrayHasKey('LBL_RECORD_BODY', $layout, "'LBL_RECORD_BODY' was not found as the default panel label on portal record views");
    }

    /**
     * Does not test additional fields as OOTB instances do not have an additional
     * fields list. Should that change in the future, add the following:
     * <code>
     * $fields = $parser->getAdditionalFields();
     * $this->assertArrayNotHasKey($this->testField, $fields, "$this->testField should not be in the additional fields list");
     * </code> 
     */
    /**
     * @group Bug56838
     */
    public function testPortalListViewLabelIsCorrect()
    {
        // SidecarListLayoutMetaDataParser
        $parser = ParserFactory::getParser(MB_PORTALLISTVIEW, self::$testModule, null, null, MB_PORTAL);
        
        // List panel defs
        $paneldefs = $parser->getPanelDefs();
        $this->assertNotEmpty($paneldefs, "Panel defs are empty for portal list view");
        $this->assertTrue(is_array($paneldefs), "Panel defs for portal list view are not an array");
        $this->assertTrue(isset($paneldefs[0]['label']), "There is no label for portal list view defs");
        $this->assertEquals($paneldefs[0]['label'], 'LBL_PANEL_DEFAULT', "Expected portal list view panel label to be 'LBL_PANEL_DEFAULT' but got '{$paneldefs[0]['label']}'");
    }
    //END SUGARCRM flav=ent ONLY
}