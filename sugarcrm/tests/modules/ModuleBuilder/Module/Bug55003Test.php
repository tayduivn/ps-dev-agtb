<?php
//FILE SUGARCRM flav=ent ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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

 
require_once 'modules/ModuleBuilder/Module/SugarPortalBrowser.php';

/**
 * Bug 55003
 * 
 * Notes showing up in portal browser and portal layout editor when Notes is not
 * a portal module
 */
class Bug55003Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * SugarPortalBrowser object
     * 
     * @var SugarPortalBrowser
     */
    protected $portalBrowser;
    
    public function setUp()
    {
        $this->portalBrowser = new SugarPortalBrowser();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        
        // Mod strings is required for Portal Browser, must use ModuleBuilder
        SugarTestHelper::setUp('mod_strings', array('ModuleBuilder'));
    }
    
    public function tearDown()
    {
        SugarTestHelper::tearDown();
        unset($this->portalBrowser);
    }
    
    /**
     * Tests known portal modules
     * 
     * @dataProvider getKnownPortalModules
     * @param string $module
     */
    public function testKnownPortalModulesPassPortalModuleCheck($module)
    {
        $assert = $this->portalBrowser->isPortalModule($module);
        $this->assertTrue($assert, "$module is a known portal module but was not found to be one in SugarPortalBrowser");
    }
    
    /**
     * Tests Notes not being a portal module
     */
    public function testNotesDoeNotPassPortalModuleCheck()
    {
        $assert = $this->portalBrowser->isPortalModule('Notes');
        $this->assertFalse($assert, "Notes is not a known portal module but was found to be one in SugarPortalBrowser");
    }
    
    /**
     * Data provider for the known modules test
     * 
     * @return array
     */
    public function getKnownPortalModules()
    {
        return array(
            array('module' => 'Bugs'),
            array('module' => 'Cases'),
            array('module' => 'Contacts'),
            array('module' => 'KBDocuments'),
        );
    }
}