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
require_once "tests/upgrade/UpgradeTestCase.php";
require_once "upgrade/scripts/post/3_UpgradeAccess.php";
require_once "install/install_utils.php";


class PostUpgradeAccessTest extends UpgradeTestCase
{
    protected $testHtacessPath = "tests/upgrade/scripts/post/.htaccess";

    public function setUp()
    {
        parent::setUp();
        $this->upgradeAccess = new SugarUpgradeUpgradeAccessTest($this->upgrader);
        $this->upgradeAccess->context = array(
            "source_dir" => dirname($this->testHtacessPath),
        );
        $htaccessContent = <<<EOQ
# Customization above restrictions

# BEGIN SUGARCRM RESTRICTIONS
# Fix mimetype for logo.svg (SP-1395)
AddType     image/svg+xml     .svg
AddType     application/json  .json
AddType     application/javascript  .js

# Customization inside restrictions

# END SUGARCRM RESTRICTIONS

# Customization below restrictions

EOQ;
        file_put_contents($this->testHtacessPath, $htaccessContent);
    }

    public function tearDown()
    {
        parent::tearDown();
        unlink($this->testHtacessPath);
    }

    /**
     * Verify that customizations to the htaccess file outside of the "sugarcrm zone" are preserved after upgrade.
     */
    public function testUpdateHtacessLeavesCustomizations()
    {
        $this->upgradeAccess->testhandleHtaccess();
        $newContent = file_get_contents($this->testHtacessPath);
        $this->assertContains("# Customization above restrictions", $newContent);
        $this->assertContains("# Customization below restrictions", $newContent);
        $this->assertNotContains("# Customization inside restrictions", $newContent);
    }


}

class SugarUpgradeUpgradeAccessTest extends SugarUpgradeUpgradeAccess {
    public function testhandleHtaccess()
    {
        $this->handleHtaccess();
    }
}
