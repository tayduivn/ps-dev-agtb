<?php
//FILE SUGARCRM flav=pro ONLY
require_once('install/install_utils.php');

class InstallUtilsTest extends Sugar_PHPUnit_Framework_TestCase
{
	public function setUp()
	{
	   
	}

	public function tearDown() 
	{
		
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
		}
	}
}
?>