<?php
//FILE SUGARCRM flav=pro ONLY
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once 'include/MetaDataManager/MetaDataManager.php';
        
class MetaDataLocationChangeTest extends Sugar_PHPUnit_Framework_TestCase
{
    //BEGIN SUGARCRM flav=ent ONLY
    protected $_expectedPortalModules = array(
        'Bugs' => 'Bugs',
        'Cases' => 'Cases',
        'Contacts' => 'Contacts',
        'KBDocuments' => 'KBDocuments',
    );
    //END SUGARCRM flav=ent ONLY
    
    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('moduleList');
    }
    
    public function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
    }
    
    /**
     * @dataProvider _mobileMetaDataFilesExistsProvider
     * @param string $module The module name
     * @param string $view The view type
     * @param string $filepath The path to the metadata file
     */
    public function testMobileMetaDataFilesExists($module, $view, $filepath)
    {
        $exists = file_exists($filepath);
        $this->assertTrue($exists, "Mobile metadata file for $view view of the $module module was not found");
    }
    
    //BEGIN SUGARCRM flav=ent ONLY
    /**
     * @dataProvider _portalMetaDataFilesExistsProvider
     * @param string $module The module name
     * @param string $view The view type
     * @param string $filepath The path to the metadata file
     */
    public function testPortalMetaDataFilesExists($module, $view, $filepath)
    {
        $exists = file_exists($filepath);
        $this->assertTrue($exists, "Portal metadata file for $view view of the $module module was not found");
    }
    //END SUGARCRM flav=ent ONLY
    
    /**
     * @dataProvider _platformList
     * @param string $platform The platform to test
     */
    public function testMetaDataManagerReturnsCorrectPlatformResults($platform)
    {
        $mm = new MetaDataManager($GLOBALS['current_user'], array($platform));
        $data = $mm->getModuleViews('Bugs');
        $this->assertTrue(isset($data['list']['meta']['panels']), "Panels meta array for detail not set for $platform platform of Bugs module");
        $this->assertTrue(isset($data['detail']['meta']['panels']), "Panels meta array for detail not set for $platform platform of Bugs module");
        $this->assertTrue(isset($data['edit']['meta']['panels']), "Panels meta array for detail not set for $platform platform of Bugs module");
    }
    
    //BEGIN SUGARCRM flav=ent ONLY
    public function testPortalModulesAreCorrect()
    {
        $mm = new MetaDataManager($GLOBALS['current_user'], array('portal'));
        $data = $mm->getModuleList('portal');
        foreach ($this->_expectedPortalModules as $module) {
            $this->assertNotEmpty($data[$module], "$module module not found in module list");
        }
    }
    
    public function testPortalLayoutsAreCorrect()
    {
        require_once 'modules/ModuleBuilder/Module/SugarPortalBrowser.php';
        $pb = new SugarPortalBrowser();
        $nodes = $pb->getNodes();
        $this->assertNotEmpty($nodes[2]);
        
        $layoutNode = $nodes[2];
        $this->assertNotEmpty($layoutNode['children']);
        
        foreach ($layoutNode['children'] as $child) {
            $this->assertTrue(isset($child['module']), 'Module is not set in a child node');
            $this->assertNotEmpty($this->_expectedPortalModules[$child['module']], "$child[module] not found in expected portal modules");
            $this->assertNotEmpty($child['children'], 'Children of the child not set');
            $hasDetailView = $this->_hasDetailViewLink($child['children']);
            $this->assertTrue($hasDetailView, "$child[module] does not have a detail view link");
        }
    }
    //END SUGARCRM flav=ent ONLY
    
    public function _mobileMetaDataFilesExistsProvider()
    {
        return array(
            array('module' => 'Accounts', 'view' => 'edit', 'filepath' => 'modules/Accounts/clients/mobile/views/edit/edit.php'),
            array('module' => 'Bugs', 'view' => 'list', 'filepath' => 'modules/Bugs/clients/mobile/views/list/list.php'),
            array('module' => 'Calls', 'view' => 'search', 'filepath' => 'modules/Calls/clients/mobile/views/search/search.php'),
            array('module' => 'Cases', 'view' => 'detail', 'filepath' => 'modules/Cases/clients/mobile/views/detail/detail.php'),
            array('module' => 'Contacts', 'view' => 'edit', 'filepath' => 'modules/Contacts/clients/mobile/views/edit/edit.php'),
            array('module' => 'Employees', 'view' => 'list', 'filepath' => 'modules/Employees/clients/mobile/views/list/list.php'),
            array('module' => 'Leads', 'view' => 'search', 'filepath' => 'modules/Leads/clients/mobile/views/search/search.php'),
            array('module' => 'Meetings', 'view' => 'detail', 'filepath' => 'modules/Meetings/clients/mobile/views/detail/detail.php'),
        );
    }
    
    //BEGIN SUGARCRM flav=ent ONLY
    public function _portalMetaDataFilesExistsProvider()
    {
        return array(
            array('module' => 'Bugs', 'view' => 'detail', 'filepath' => 'modules/Bugs/clients/portal/views/detail/detail.php'),
            array('module' => 'Cases', 'view' => 'list', 'filepath' => 'modules/Cases/clients/portal/views/list/list.php'),
            array('module' => 'Contacts', 'view' => 'edit', 'filepath' => 'modules/Contacts/clients/portal/views/edit/edit.php'),
            array('module' => 'KBDocuments', 'view' => 'detail', 'filepath' => 'modules/KBDocuments/clients/portal/views/detail/detail.php'),
        );
    }
    //END SUGARCRM flav=ent ONLY
    
    public function _platformList()
    {
        return array(
            //BEGIN SUGARCRM flav=ent ONLY
            array('platform' => 'portal'),
            //END SUGARCRM flav=ent ONLY
            array('platform' => 'mobile'),
        );
    }
    
    //BEGIN SUGARCRM flav=ent ONLY
    protected function _hasDetailViewLink($child) 
    {
        foreach ($child as $props) {
            if (isset($props['action']) && strpos($props['action'], 'DetailView') !== false) {
                return true;
            }
        }
        
        return false;
    }
    //END SUGARCRM flav=ent ONLY
}