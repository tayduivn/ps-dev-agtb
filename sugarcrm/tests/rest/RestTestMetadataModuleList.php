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

require_once('tests/rest/RestTestBase.php');

class RestTestMetadataModuleList extends RestTestBase {
    //BEGIN SUGARCRM flav=ent ONLY
    public $oppTestPath ='modules/Opportunities/clients/portal/views/list/list.php';
    //END SUGARCRM flav=ent ONLY    
    public $unitTestFiles = array();
    public $createdStudioFile = false;
    
    public function setUp()
    {
        parent::setUp();
        
        //BEGIN SUGARCRM flav=ent ONLY
        $this->unitTestFiles[] = $this->oppTestPath;
        if (!file_exists('modules/Opportunities/metadata/studio.php')) {
            sugar_file_put_contents('modules/Opportunities/metadata/studio.php', '<?php' . "\n\$time = time();");
            $this->createdStudioFile = true;
        }
        //END SUGARCRM flav=ent ONLY
        //BEGIN SUGARCRM flav=pro ONLY
        $this->unitTestFiles[] = 'custom/include/MVC/Controller/wireless_module_registry.php';
        //END SUGARCRM flav=pro ONLY
    }
    
    public function tearDown()
    {
        foreach($this->unitTestFiles as $unitTestFile ) {
            if ( file_exists($unitTestFile) ) {
                // Ignore the warning on this, the file stat cache causes the file_exist to trigger even when it's not really there
                @unlink($this->oppTestPath);
            }
        }
        
        if ($this->createdStudioFile && file_exists('modules/Opportunities/metadata/studio.php')) {
            unlink('modules/Opportunities/metadata/studio.php');
        }
        parent::tearDown();
    }

    //BEGIN SUGARCRM flav=ent ONLY
    public function testMetadataGetModuleListPortal() {
        // Setup the tab controller here and get the default tabs for setting and resetting
        require_once('modules/MySettings/TabController.php');
        $tabs = new TabController();
        $defaultTabs = $tabs->get_tabs_system();
        
        $restReply = $this->_restCall('metadata?type_filter=module_list&platform=portal');

        $this->assertTrue(isset($restReply['reply']['module_list']['_hash']),'There is no portal module list');
        // There should only be the following modules by default: Bugs, Cases, KBDocuments, Leads
        $enabledPortal = array('Cases','Contacts');
        $restModules = $restReply['reply']['module_list'];

        unset($restModules['_hash']);
        foreach ( $enabledPortal as $module ) {
            $this->assertTrue(in_array($module,$restModules),'Module '.$module.' missing from the portal module list.');
        }
        
        // Although there are 4 OOTB portal modules, only 2 are enabled by default
        $this->assertEquals(2,count($restModules),'There are extra modules in the portal module list');
        // add module
        
        $newModuleList = array('Home','Accounts','Contacts','Opportunities','Bugs','Leads','Calendar','Reports','Quotes','Documents','Emails','Campaigns','Calls','Meetings','Tasks','Notes','Forecasts','Cases','Prospects','ProspectLists');
        
        $tabs->set_system_tabs($newModuleList);
        $restReply = $this->_restCall('metadata?type_filter=module_list&platform=portal');

        $this->assertTrue(isset($restReply['reply']['module_list']['_hash']),'There is no portal module list');
        // There should only be the following modules by default: Bugs, Cases, KBDocuments, Contacts
        // And now 3 are enabled
        $enabledPortal = array('Cases','Contacts', 'Bugs');
        $restModules = $restReply['reply']['module_list'];

        unset($restModules['_hash']);
        foreach ( $enabledPortal as $module ) {
            $this->assertTrue(in_array($module,$restModules),'Module '.$module.' missing from the portal module list.');
        }
        $this->assertEquals(3,count($restModules),'There are extra modules in the portal module list');

        // Set to include Opportunities
        $newModuleList = array('Home','Accounts','Contacts','Opportunities','Leads','Calendar','Reports','Quotes','Documents','Emails','Campaigns','Calls','Meetings','Tasks','Notes','Forecasts','Cases','Prospects','ProspectLists');
        
        $tabs->set_system_tabs($newModuleList);

        // Now add an extra file and make sure it gets picked up
        if (is_dir($dir = dirname($this->oppTestPath)) === false) {
            sugar_mkdir($dir, null, true);
        }
        sugar_file_put_contents($this->oppTestPath, "<?php\n\$viewdefs['Opportunities']['portal']['view']['list'] = array('test' => 'Testing');");
        $restReply = $this->_restCall('metadata?type_filter=module_list&platform=portal');

        $this->assertTrue(in_array('Opportunities',$restReply['reply']['module_list']),'The new Opportunities module did not appear in the portal list');
        
        // Set the tabs back to what they were
        $tabs->set_system_tabs($defaultTabs[0]);
    }
    //END SUGARCRM flav=ent ONLY
    
    //BEGIN SUGARCRM flav=pro ONLY
    public function testMetadataGetModuleListMobile() {
        $restReply = $this->_restCall('metadata?type_filter=module_list&platform=mobile');

        foreach ( array ( '','custom/') as $prefix) {
            if(file_exists($prefix.'include/MVC/Controller/wireless_module_registry.php')){
                require($prefix.'include/MVC/Controller/wireless_module_registry.php');
            }
        }
        
        // $wireless_module_registry is defined in the file loaded above
        $enabledMobile = array_keys($wireless_module_registry);


        $this->assertTrue(isset($restReply['reply']['module_list']['_hash']),'There is no mobile module list');
        $restModules = $restReply['reply']['module_list'];
        unset($restModules['_hash']);
        foreach ( $enabledMobile as $module ) {
            $this->assertTrue(in_array($module,$restModules),'Module '.$module.' missing from the mobile module list.');
        }
        $this->assertEquals(count($enabledMobile),count($restModules),'There are extra modules in the mobile module list');
        
        // Create a custom set of wireless modules to test if it is loading those properly
        if ( !is_dir('custom/include/MVC/Controller/') ) {
            mkdir('custom/include/MVC/Controller',0755,true);
        }
        file_put_contents('custom/include/MVC/Controller/wireless_module_registry.php','<'."?php\n".'$wireless_module_registry = array("Accounts"=>"Accounts","Contacts"=>"Contacts","Opportunities"=>"Opportunities");');
        
        $enabledMobile = array('Accounts','Contacts','Opportunities');

        $restReply = $this->_restCall('metadata?type_filter=module_list&platform=mobile');
        $this->assertTrue(isset($restReply['reply']['module_list']['_hash']),'There is no mobile module list on the second pass');
        $restModules = $restReply['reply']['module_list'];
        unset($restModules['_hash']);
        foreach ( $enabledMobile as $module ) {
            $this->assertTrue(in_array($module,$restModules),'Module '.$module.' missing from the mobile module list on the second pass');
        }
        $this->assertEquals(count($enabledMobile),count($restModules),'There are extra modules in the mobile module list on the second pass');

        
    }
    //END SUGARCRM flav=pro ONLY

    public function testMetadataGetModuleListBase() {
        $restReply = $this->_restCall('metadata?type_filter=module_list');

        $this->assertTrue(isset($restReply['reply']['module_list']['_hash']),'There is no base module list');
        $restModules = $restReply['reply']['module_list'];
        unset($restModules['_hash']);
        require_once("modules/MySettings/TabController.php");
        $tc = new TabController();
        $enabledModules = $tc->get_user_tabs($this->_user);
        $enabledBase = array_keys($enabledModules);
        foreach ( $enabledBase as $module ) {
            $this->assertTrue(in_array($module,$restModules),'Module '.$module.' missing from the base module list.');
        }
        $this->assertEquals(count($enabledBase),count($restModules),'There are extra modules in the base module list');
        
    }

    public function testMetadataGetFullModuleListBase() {
        $restReply = $this->_restCall('metadata?type_filter=full_module_list');
        $fullRestModules = $restReply['reply']['full_module_list'];
        $this->assertTrue(isset($restReply['reply']['full_module_list']['_hash']),'There is no base module list');
        $tc = new TabController();
        $enabledModules = $tc->get_user_tabs($this->_user);
        $enabledBase = array_keys($enabledModules);
        foreach ( $enabledBase as $module ) {
            $this->assertTrue(in_array($module,$fullRestModules),'Module '.$module.' missing from the full module list.');
        }

        $this->assertGreaterThan(count($enabledBase),count($fullRestModules),'There are too many modules in the full list');
    }

}
