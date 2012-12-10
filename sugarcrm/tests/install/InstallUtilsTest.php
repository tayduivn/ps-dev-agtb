<?php
//FILE SUGARCRM flav=pro ONLY
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
        handleSidecarConfig();
        $this->assertFileExists('config.js');
        $configJSContents = file_get_contents('config.js');

        $this->assertNotEmpty($configJSContents);
        $this->assertRegExp('/\"platform\"\s*?\:\s*?\"base\"/', $configJSContents);
        $this->assertRegExp('/\"clientID\"\s*?\:\s*?\"sugar\"/', $configJSContents);
        $this->assertRegExp('/\"authStore\"\s*?\:\s*?\"sugarAuthStore\"/', $configJSContents);
        $this->assertRegExp('/\"keyValueStore\"\s*?\:\s*?\"sugarAuthStore\"/', $configJSContents);
    }
    //END SUGARCRM flav=pro ONLY
}
?>