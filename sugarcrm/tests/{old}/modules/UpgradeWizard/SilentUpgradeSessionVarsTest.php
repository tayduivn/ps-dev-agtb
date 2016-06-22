<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

use PHPUnit\Framework\TestCase;

class SilentUpgradeSessionVarsTest extends TestCase
{
    private $varsCacheFileName;
    
    public function setUp() 
    {
        sugar_cache_clear('silent_upgrade_vars_cache');
    }

    public function testSilentUpgradeSessionVars()
    {
    	require_once('modules/UpgradeWizard/uw_utils.php');
    	$loaded = loadSilentUpgradeVars();
    	$this->assertTrue($loaded, "Could not load the silent upgrade vars");
    	global $silent_upgrade_vars_loaded;

    	$this->assertNotEmpty($silent_upgrade_vars_loaded, "\$silent_upgrade_vars_loaded array should not be empty");
    	
    	$set = setSilentUpgradeVar('SDizzle', 'BSnizzle');
    	$this->assertTrue($set, "Could not set a silent upgrade var");
    	
    	$get = getSilentUpgradeVar('SDizzle');
    	$this->assertEquals('BSnizzle', $get, "Unexpected value when getting silent upgrade var before resetting");
    	
        $output = getSilentUpgradeVar('SDizzle');
    	
    	$this->assertEquals('BSnizzle', $output, "Running custom script didn't successfully retrieve the value");
    	
    	removeSilentUpgradeVarsCache();
    	$this->assertEmpty($silent_upgrade_vars_loaded, "Silent upgrade vars variable should have been unset in removeSilentUpgradeVarsCache() call");

    	$get = getSilentUpgradeVar('SDizzle');
    	$this->assertNotEquals('BSnizzle', $get, "Unexpected value when getting silent upgrade var after resetting");
    }
}
