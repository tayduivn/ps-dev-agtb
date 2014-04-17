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
 * Copyright  2004-2014 SugarCRM Inc.  All rights reserved.
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
