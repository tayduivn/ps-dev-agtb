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
 
class SilentUpgradeSessionVarsTest extends Sugar_PHPUnit_Framework_TestCase 
{
    private $externalTestFileName = 'test_silent_upgrade_vars.php';
    
    public function setUp() 
    {
        $this->writeExternalTestFile();
    }

    public function tearDown() 
    {
        $this->removeExternalTestFile();
    }

    public function testSilentUpgradeSessionVars()
    {
    	
    	require_once('modules/UpgradeWizard/uw_utils.php');
    	
    	$varsCacheFileName = "{$GLOBALS['sugar_config']['cache_dir']}/silentUpgrader/silentUpgradeCache.php";
        
    	$loaded = loadSilentUpgradeVars();
    	$this->assertTrue($loaded, "Could not load the silent upgrade vars");
    	global $silent_upgrade_vars_loaded;
    	$this->assertTrue(!empty($silent_upgrade_vars_loaded), "\$silent_upgrade_vars_loaded array should not be empty");
    	
    	$set = setSilentUpgradeVar('SDizzle', 'BSnizzle');
    	$this->assertTrue($set, "Could not set a silent upgrade var");
    	
    	$get = getSilentUpgradeVar('SDizzle');
    	$this->assertEquals('BSnizzle', $get, "Unexpected value when getting silent upgrade var before resetting");
    	
    	$write = writeSilentUpgradeVars();
    	$this->assertTrue($write, "Could not write the silent upgrade vars to the cache file. Function returned false");
    	$this->assertFileExists($varsCacheFileName, "Cache file doesn't exist after call to writeSilentUpgradeVars()");
    	
    	$output = shell_exec("php {$this->externalTestFileName}");
    	
    	$this->assertEquals('BSnizzle', $output, "Running custom script didn't successfully retrieve the value");
    	
    	$remove = removeSilentUpgradeVarsCache();
    	$this->assertTrue(empty($silent_upgrade_vars_loaded), "Silent upgrade vars variable should have been unset in removeSilentUpgradeVarsCache() call");
    	$this->assertFileNotExists($varsCacheFileName, "Cache file exists after call to removeSilentUpgradeVarsCache()");
    	
    	$get = getSilentUpgradeVar('SDizzle');
    	$this->assertNotEquals('BSnizzle', $get, "Unexpected value when getting silent upgrade var after resetting");
    }
    
    private function writeExternalTestFile()
    {
        $externalTestFileContents = <<<EOQ
<?php
        
        define('sugarEntry', true);
        require_once('include/entryPoint.php');
        require_once('modules/UpgradeWizard/uw_utils.php');
        
        \$get = getSilentUpgradeVar('SDizzle');
        
        echo \$get;
EOQ;
        
        file_put_contents($this->externalTestFileName, $externalTestFileContents);
    }
    
    private function removeExternalTestFile()
    {
        if(file_exists($this->externalTestFileName))
        {
            unlink($this->externalTestFileName);
        }
    }
}
?>
