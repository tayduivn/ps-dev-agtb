<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/
require_once('install/install_utils.php');

class InstallUtilsTest extends Sugar_PHPUnit_Framework_TestCase
{
    private static $configJSContents;

    public static function setUpBeforeClass()
    {
        if(file_exists('config.js')) {
           self::$configJSContents = file_get_contents('config.js');
           unlink('config.js');
        }
    }

    public static function tearDownAfterClass()
    {
        //If we had existing config.js content, copy it back in
        if(!empty(self::$configJSContents)) {
            file_put_contents('config.js', self::$configJSContents);
        }
    }

	public function testParseAcceptLanguage()
	{
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.8';
       	$lang = parseAcceptLanguage();
       	$this->assertEquals('en_us,en', $lang, 'parse_accept_language did not return proper values');
	}

	public function testRemoveConfig_SIFile(){
		if(write_array_to_file('config_si', array(), 'config_si.php')) {
			removeConfig_SIFile();
			$this->assertFileNotExists('config_si.php', 'removal of config_si did not succeed');
			SugarAutoLoader::delFromMap('config_si.php');
		}
	}

    //BEGIN SUGARCRM flav=pro ONLY
    /**
     * This is a test to check the creation of the config.js file used by the sidecar framework beginning in the 6.7 release.
     * In the future this configuration may move to be contained within a database.
     */
    public function testHandleSidecarConfig()
    {
        $file = sugar_cached('config.js');
        handleSidecarConfig();
        $this->assertFileExists($file);
        $configJSContents = file_get_contents($file);

        $this->assertNotEmpty($configJSContents);
        $this->assertRegExp('/\"platform\"\s*?\:\s*?\"base\"/', $configJSContents);
        $this->assertRegExp('/\"clientID\"\s*?\:\s*?\"sugar\"/', $configJSContents);
    }
    //END SUGARCRM flav=pro ONLY
}
