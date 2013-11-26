<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc. All rights reserved.
 */

require_once 'include/MetaDataManager/MetaDataManager.php';

class MetaDataManagerDisplayModuleListTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var TabController Instance of TabController
     */
    static protected $tabs;

    /**
     * @var array Test set of tabs
     */
    static protected $testTabs = array(
        'Accounts' => 'Accounts',
        'Contacts' => 'Contacts',
        'Leads' => 'Leads',
        'Opportunities' => 'Opportunities',
        'Cases' => 'Cases',
        'Bugs' => 'Bugs'
    );

    /**
     * @var array Store current system tabs to backup later
     */
    static protected $savedSystemTabs;

    //BEGIN SUGARCRM flav=ent ONLY
    /**
     * @var array Store current portal tabs to backup later
     */
    static protected $savedPortalTabs;
    //END SUGARCRM flav=ent ONLY

    //BEGIN SUGARCRM flav=pro ONLY
    /**
     * @var string Location of the mobile tabs metadata file
     */
    static protected $mobileMetaFile = 'include/MVC/Controller/wireless_module_registry.php';

    /**
     * @var string Location of the custom mobile tabs metadata file
     */
    static protected $customMobileMetaFile = 'custom/include/MVC/Controller/wireless_module_registry.php';

    /**
     * @var bool Flag to indicate if the mobile custom file already exists
     */
    static protected $mobileBackedUp = false;

    /**
     * @var bool Flag to indicate if the mobile custom path exists
     */
    static protected $mobileCustomPathExists = true;

    /**
     * @var string Path that is created for test purpose.
     */
    static protected $mobileCreatedPath;
    //END SUGARCRM flav=pro ONLY

    /**
     * Set up once before all tests are run
     */
    static public function setUpBeforeClass()
    {
        SugarTestHelper::setUp('current_user', array(true, 1));

        self::$tabs = new TabController();

        // Save current system tabs and set test system tabs
        self::$savedSystemTabs = self::$tabs->get_system_tabs();
        self::$tabs->set_system_tabs(self::$testTabs);

        //BEGIN SUGARCRM flav=ent ONLY
        // Save current portal tabs and set test portal tabs
        self::$savedPortalTabs = self::$tabs->getPortalTabs();
        self::$tabs->setPortalTabs(array_keys(self::$testTabs));
        //END SUGARCRM flav=ent ONLY

        //BEGIN SUGARCRM flav=pro ONLY
        self::setUpMobile();
        //END SUGARCRM flav=pro ONLY
    }

    /**
     * Tear down once after all tests are run
     */
    static public function tearDownAfterClass()
    {
        // Reset saved system tabs
        self::$tabs->set_system_tabs(self::$savedSystemTabs);

        //BEGIN SUGARCRM flav=ent ONLY
        // Reset saved portal tabs
        self::$tabs->setPortalTabs(self::$savedPortalTabs);
        //END SUGARCRM flav=ent ONLY

        //BEGIN SUGARCRM flav=pro ONLY
        self::tearDownMobile();
        //END SUGARCRM flav=pro ONLY

        SugarTestHelper::tearDown();
    }

    /**
     * Test getDisplayModuleList method for the base app
     *
     * @group MetaDataManager
     */
    public function testBaseGetDisplayModuleList()
    {
        // Run the test
        $mm = MetaDataManager::getManager('base');
        $expectedTabs = array_keys(self::$testTabs);
        $this->assertEquals($expectedTabs, $mm->getDisplayModuleList());
    }

    //BEGIN SUGARCRM flav=ent ONLY
    /**
     * Test getDisplayModuleList method for portal
     *
     * @group MetaDataManager
     */
    public function testPortalGetDisplayModuleList()
    {
        // Run the test
        $mm = MetaDataManager::getManager('portal');
        $expectedTabs = array_keys(self::$testTabs);
        $this->assertEquals($expectedTabs, $mm->getDisplayModuleList());
    }
    //END SUGARCRM flav=ent ONLY

    //BEGIN SUGARCRM flav=pro ONLY
    /**
     * Test getDisplayModuleList method for mobile
     *
     * @group MetaDataManager
     */
    public function testMobileGetDisplayModuleList()
    {
        // Run the test
        $mm = MetaDataManager::getManager('mobile');
        $expectedTabs = array_keys(self::$testTabs);
        $this->assertEquals($expectedTabs, $mm->getDisplayModuleList());
    }
    //END SUGARCRM flav=pro ONLY

    //BEGIN SUGARCRM flav=pro ONLY
    static protected function setUpMobile()
    {
        if (file_exists(self::$customMobileMetaFile)) {
            // Backup the custom file if there is one
            self::$mobileBackedUp = true;
            rename(self::$customMobileMetaFile, self::$customMobileMetaFile . '.backup');
        } else if (!is_dir(dirname(self::$customMobileMetaFile))) {
            // If the custom path does not exist, we are gonna find the first
            // non existing folder of this path, se we can clean up later
            self::$mobileCustomPathExists = false;
            $customFolders = explode('/', dirname(self::$customMobileMetaFile));
            self::$mobileCreatedPath = '';
            foreach ($customFolders as $folder) {
                if (!empty(self::$mobileCreatedPath)) {
                    self::$mobileCreatedPath .= '/';
                }
                self::$mobileCreatedPath .= $folder;
                if (!is_dir(self::$mobileCreatedPath)) {
                    // This path does not exist. We'll have to start cleaning up
                    // from here.
                    break;
                }
            }
        }

        // Create a custom `wireless_module_registry.php` file
        // Module list must match self::$testTabs
        $testFileContents = <<<EOF
<?php
\$wireless_module_registry = array(
	'Accounts' => array(),
	'Contacts' => array(),
	'Leads' => array(),
	'Opportunities' => array('disable_create' => true),
	'Cases' => array('disable_create' => true),
	'Bugs' => array(),
);
EOF;
        // If no custom file, need to create custom directory
        if (!self::$mobileBackedUp) {
            $filename = create_custom_directory(self::$mobileMetaFile);
        }

        // Create the custom file
        file_put_contents(self::$customMobileMetaFile, $testFileContents);
        SugarAutoLoader::addToMap(self::$customMobileMetaFile);

    }

    static protected function tearDownMobile()
    {
        // Reset backed-up custom file
        if (self::$mobileBackedUp) {
            rename(self::$customMobileMetaFile . '.backup', self::$customMobileMetaFile);
        } else {
            // Clean up custom path
            if (self::$mobileCustomPathExists) {
                unlink(self::$customMobileMetaFile);
            } else {
                _ppl(self::$mobileCreatedPath);
                rmdir_recursive(self::$mobileCreatedPath);
            }
            SugarAutoLoader::delFromMap(self::$customMobileMetaFile);
        }
    }
    //END SUGARCRM flav=pro ONLY
}
