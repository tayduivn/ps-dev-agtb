<?php
//FILE SUGARCRM flav=ent ONLY
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

require_once 'tests/rest/RestTestPortalBase.php';
class RestMetadataModuleListPortalTest extends RestTestPortalBase {
    public $createdStudioFile = false;
    public $unitTestFiles = array();
    public $oppTestPath ='modules/Accounts/clients/portal/views/list/list.php';

    public function setUp()
    {
        parent::setUp();
        // Portal test needs this one, tear down happens in parent
        SugarTestHelper::setup('mod_strings', array('ModuleBuilder'));

        $this->unitTestFiles[] = $this->oppTestPath;
        if (!file_exists('modules/Accounts/metadata/studio.php')) {
            SugarAutoLoader::put('modules/Accounts/metadata/studio.php', '<?php' . "\n\$time = time();", true);
            $this->createdStudioFile = true;
        }

    }

    public function tearDown()
    {
        if ($this->createdStudioFile && file_exists('modules/Accounts/metadata/studio.php')) {
            SugarAutoLoader::unlink('modules/Accounts/metadata/studio.php');
        }

        foreach($this->unitTestFiles as $unitTestFile ) {
            if ( file_exists($unitTestFile) ) {
                // Ignore the warning on this, the file stat cache causes the file_exist to trigger even when it's not really there
                SugarAutoLoader::unlink($unitTestFile);
            }
        }

        if (file_exists($this->oppTestPath)) {
            SugarAutoLoader::unlink($this->oppTestPath);
        }
        SugarAutoLoader::saveMap();
        // Set the tabs back to what they were
        if ( isset($this->defaultTabs[0]) ) {
            require_once('modules/MySettings/TabController.php');
            $tabs = new TabController();

            $tabs->set_system_tabs($this->defaultTabs[0]);
            $GLOBALS['db']->commit();
        }

        parent::tearDown();
    }

    // Need to set the platform to something else
    protected function _restLogin($username = '', $password = '', $platform = 'portal')
    {
        return parent::_restLogin($username,$password,$platform);
    }
    /**
     * @group rest
     */
    public function testMetadataGetModuleListPortal() {
        // Setup the tab controller here and get the default tabs for setting and resetting
        require_once('modules/MySettings/TabController.php');
        $tabs = new TabController();
        $this->defaultTabs = $tabs->get_tabs_system();

        $this->_clearMetadataCache();
        $restReply = $this->_restCall('me');

        $this->assertTrue(isset($restReply['reply']['current_user']['module_list']),'There is no portal module list');
        // There should only be the following modules by default: Bugs, Cases, KBDocuments, Leads
        $enabledPortal = array('Cases','Contacts');
        $restModules = $restReply['reply']['current_user']['module_list'];

        unset($restModules['_hash']);
        foreach ( $enabledPortal as $module ) {
            $this->assertTrue(in_array($module,$restModules),'Module '.$module.' missing from the portal module list.');
        }
        // Bugs and KBDocuments are sometimes enabled, and they are fine, just not in the normal list
        $idx = array_search('Bugs',$restModules);
        if ( is_int($idx) ) {
            unset($restModules[$idx]);
        }
        $idx = array_search('KBDocuments',$restModules);
        if ( is_int($idx)) {
            unset($restModules[$idx]);
        }
        // Although there are 4 OOTB portal modules, only 2 are enabled by default
        $this->assertEquals(2,count($restModules),'There are extra modules in the portal module list');
        // add module

        $newModuleList = array('Home','Accounts','Contacts','Opportunities','Bugs','Leads','Calendar','Reports','Quotes','Documents','Emails','Campaigns','Calls','Meetings','Tasks','Notes','Forecasts','Cases','Prospects','ProspectLists');

        $tabs->set_system_tabs($newModuleList);
        $GLOBALS['db']->commit();
        // Do this to load the tab list into cache
        $tabs->get_tabs_system();
        $this->_clearMetadataCache();
        $restReply = $this->_restCall('me');

        $this->assertTrue(isset($restReply['reply']['current_user']['module_list']),'There is no portal module list');
        // There should only be the following modules by default: Bugs, Cases, KBDocuments, Contacts
        // And now 3 are enabled
        $enabledPortal = array('Cases','Contacts', 'Bugs');
        $restModules = $restReply['reply']['current_user']['module_list'];

        unset($restModules['_hash']);
        foreach ( $enabledPortal as $module ) {
            $this->assertTrue(in_array($module,$restModules),'Module '.$module.' missing from the portal module list.');
        }
        $this->assertEquals(3,count($restModules),'There are extra modules in the portal module list');

        // Set to include Opportunities
        $newModuleList = array('Home','Accounts','Contacts','Opportunities','Leads','Calendar','Reports','Quotes','Documents','Emails','Campaigns','Calls','Meetings','Tasks','Notes','Forecasts','Cases','Prospects','ProspectLists');

        $tabs->set_system_tabs($newModuleList);
        $GLOBALS['db']->commit();
        // Do this to load the tab list into cache
        $tabs->get_tabs_system();
        // Now add an extra file and make sure it gets picked up
        if (is_dir($dir = dirname($this->oppTestPath)) === false) {
            sugar_mkdir($dir, null, true);
        }
        SugarAutoLoader::put($this->oppTestPath, "<?php\n\$viewdefs['Accounts']['portal']['view']['list'] = array('test' => 'Testing');", true);
        $this->_clearMetadataCache();
        $restReply = $this->_restCall('me');

        $this->assertTrue(in_array('Accounts',$restReply['reply']['current_user']['module_list']),'The new Accounts module did not appear in the portal list');

    }

    /**
     * @group rest
     * @group Bug56911
     */
    public function testPortalMetadataModulesContainsNotes()
    {
        // Get the metadata for portal
        $restReply = $this->_restCall('metadata?type_filter=modules&platform=portal');
        $this->assertArrayHasKey('modules', $restReply['reply'], "The modules index is missing from the response");
        $this->assertArrayHasKey('Notes', $restReply['reply']['modules'], 'Notes was not returned in the modules metadata as expected');
    }
}