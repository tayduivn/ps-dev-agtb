<?php
/**
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2014 SugarCRM Inc.  All rights reserved.
 */

require_once "tests/upgrade/UpgradeTestCase.php";
require_once 'upgrade/scripts/post/7_MBMenu.php';

/**
 * Class CRYS174Test tests vCard menu item creation
 */
class CRYS174Test extends UpgradeTestCase
{
    private $configBackup = array();

    public function setUp()
    {
        parent::setUp();
        $this->configBackup = $this->upgrader->config;
        $this->upgrader->config['default_permissions'] = array();
    }

    public function tearDown()
    {
        $this->upgrader->config = $this->configBackup;
        parent::tearDown();
    }

    /**
     * Data provider for testVCardMenuItemCreation
     *
     * @return array with Module Name under test and Should vCard item be created or not
     */
    public function modulesProvider()
    {
        return array(
            array('Contacts', true),
            array('Accounts', false),
        );
    }

    /**
     * @dataProvider modulesProvider
     *
     * @group CRYS174
     */
    public function testVCardMenuItemCreation($moduleName, $menuItemShouldBeCreated)
    {
        $menuFile = "modules/$moduleName/clients/base/menus/header/header.php";
        SugarTestHelper::saveFile($menuFile);
        file_put_contents($menuFile, '');

        $scriptObject = new SugarUpgradeMBMenu($this->upgrader);
        SugarTestReflection::callProtectedMethod($scriptObject, 'addMenu', array($moduleName));
        $contents = file_get_contents($menuFile);

        $stringToLookFor = "#$moduleName/vcard-import";
        if ($menuItemShouldBeCreated) {
            $this->assertContains($stringToLookFor, $contents);
        } else {
            $this->assertNotContains($stringToLookFor, $contents);
        }
    }
}
