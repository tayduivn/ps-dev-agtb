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

require_once 'modules/ModuleBuilder/parsers/ParserFactory.php';

class Bug59825Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * The test module
     * 
     * @var string
     */
    protected static $_module = 'Bugs';

    /**
     * Rather than setting up and tearing down for each iteration of the data 
     * provider, set up once and tear down once, as these are used as-is throughout
     * each test.
     */
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('current_user', array(true, true)); // Admin user
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('moduleList');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('mod_strings', array('ModuleBuilder'));
    }
    
    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Tests not null parsers for views
     * 
     * @param string $type A type of view to get a parser for
     * @dataProvider _layoutProvider
     */
    public function testParserIsNotNullForLayoutType($type)
    {
        $parser = ParserFactory::getParser($type, self::$_module);
        $this->assertNotNull($parser, "Portal parser for $type in Bugs module is null");
    }

    /**
     * Gets a list of 'types' of metadata views to be used in the test. Includes
     * basic layouts from all OOTB and, where applicable, wireless and portal
     * layouts.
     * 
     * @return array
     */
    public function _layoutProvider()
    {
        return array(
            // Basic types for all OOTB installations
            // This simulates StudioModule::getViewMetadataSources()
            array('type' => MB_EDITVIEW),
            array('type' => MB_DETAILVIEW),
            array('type' => MB_LISTVIEW),
            array('type' => MB_BASICSEARCH),
            array('type' => MB_ADVANCEDSEARCH),
            array('type' => MB_DASHLET),
            array('type' => MB_DASHLETSEARCH),
            array('type' => MB_POPUPLIST),
            array('type' => MB_QUICKCREATE),
            //BEGIN SUGARCRM flav=pro ONLY
            // Wireless types
            // This simulates StudioModule::getWirelessLayouts()
            array('type' => MB_WIRELESSEDITVIEW),
            array('type' => MB_WIRELESSDETAILVIEW),
            array('type' => MB_WIRELESSLISTVIEW),
            array('type' => MB_WIRELESSBASICSEARCH),
            //END SUGARCRM flav=pro ONLY
            //BEGIN SUGARCRM flav=ent ONLY
            // Portal types not including search, which was the cause of the bug
            // This simulates StudioModule::getPortalLayoutSources()
            array('type' => MB_PORTALRECORDVIEW),
            array('type' => MB_PORTALLISTVIEW),
            //END SUGARCRM flav=ent ONLY
        );
    }
}