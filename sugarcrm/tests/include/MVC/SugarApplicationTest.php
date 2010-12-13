<?php
require_once 'include/MVC/SugarApplication.php';

class SugarApplicationTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $_app;
    // Run in isolation so that it doesn't mess up other tests
    public $inIsolation = true;

    public function setUp()
    {
        $startTime = microtime();
        $system_config = new Administration();
        $system_config->retrieveSettings();
        $GLOBALS['system_config'] = $system_config;
        $this->_app = new SugarApplication();
        if ( isset($_SESSION['authenticated_user_theme']) )
            unset($_SESSION['authenticated_user_theme']);
    }

    private function _loadUser()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $_SESSION[$GLOBALS['current_user']->user_name.'_PREFERENCES']['global']['gridline'] = 'on';
    }

    private function _removeUser()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    public function tearDown()
    {
        unset($GLOBALS['current_user']);
        unset($GLOBALS['moduleList']);
        unset($GLOBALS['request_string']);
        unset($GLOBALS['adminOnlyList']);
        unset($GLOBALS['modListHeader']);
        unset($GLOBALS['modInvisList']);
        unset($GLOBALS['app_strings']);
        unset($GLOBALS['system_config']);
        unset($GLOBALS['app_list_strings']);
        unset($GLOBALS['mod_strings']);
        unset($GLOBALS['theme']);
        unset($GLOBALS['image_path']);
        unset($GLOBALS['starttTime']);
        unset($GLOBALS['sugar_version']);
        unset($GLOBALS['sugar_flavor']);
        $GLOBALS['current_language'] = $GLOBALS['sugar_config']['default_language'];
    }

    public function testSetupPrint()
    {
        $_GET['foo'] = 'bar';
        $_POST['dog'] = 'cat';
        $this->_app->setupPrint();
        $this->assertEquals($GLOBALS['request_string'],
            'foo=bar&dog=cat&print=true'
        );
    }

    /*
     * @ticket 40277
     */
    public function testSetupPrintWithMultidimensionalArray()
    {
        $_GET['foo'] = array(
                            '0' => array(
                                   '0'=>'bar',
                                   'a' => 'hej'),
                            '1' => 'notMultidemensional',
                            '2' => 'notMultidemensional',
                           );
        $_POST['dog'] = 'cat';
        $this->_app->setupPrint();
        $this->assertEquals('foo[1]=notMultidemensional&foo[2]=notMultidemensional&dog=cat&print=true', $GLOBALS['request_string']
        );
    }

    public function testLoadDisplaySettingsDefault()
    {
        $this->_loadUser();

        $this->_app->loadDisplaySettings();

        $this->assertEquals($GLOBALS['theme'],
            $GLOBALS['sugar_config']['default_theme']);

        $this->_removeUser();
    }

    public function testLoadDisplaySettingsAuthUserTheme()
    {
        $this->_loadUser();

        $_SESSION['authenticated_user_theme'] = 'Sugar';

        $this->_app->loadDisplaySettings();

        $this->assertEquals($GLOBALS['theme'],
            $_SESSION['authenticated_user_theme']);

        $this->_removeUser();
    }

    public function testLoadDisplaySettingsUserTheme()
    {
        $this->_loadUser();

        //BEGIN SUGARCRM flav!=com ONLY
        $_REQUEST['usertheme'] = 'Sugar';
        //END SUGARCRM flav!=com ONLY
        //BEGIN SUGARCRM flav=com ONLY
        $_REQUEST['usertheme'] = 'Sugar5';
        //END SUGARCRM flav=com ONLY
        
        $this->_app->loadDisplaySettings();

        $this->assertEquals($GLOBALS['theme'],
            $_REQUEST['usertheme']);

        $this->_removeUser();
    }

    public function testLoadGlobals()
    {
        $this->_app->controller =
            ControllerFactory::getController($this->_app->default_module);
        $this->_app->loadGlobals();

        $this->assertEquals($GLOBALS['currentModule'],$this->_app->default_module);
        $this->assertEquals($_REQUEST['module'],$this->_app->default_module);
        $this->assertEquals($_REQUEST['action'],$this->_app->default_action);
    }

    /**
     * @ticket 33283
     */
    public function testCheckDatabaseVersion()
    {
        if ( isset($GLOBALS['sugar_db_version']) )
            $old_sugar_db_version = $GLOBALS['sugar_db_version'];
        if ( isset($GLOBALS['sugar_version']) )
            $old_sugar_version = $GLOBALS['sugar_version'];
        include 'sugar_version.php';
        $GLOBALS['sugar_version'] = $sugar_version;

        // first test a valid value
        $GLOBALS['sugar_db_version'] = $sugar_db_version;
        $this->assertTrue($this->_app->checkDatabaseVersion(false));

        $GLOBALS['sugar_db_version'] = '1.1.1';
        // then test to see if we pull against the cache the valid value
        $this->assertTrue($this->_app->checkDatabaseVersion(false));

        // now retest to be sure we actually do the check again
        sugar_cache_put('checkDatabaseVersion_row_count', 0);
        $this->assertFalse($this->_app->checkDatabaseVersion(false));

        if ( isset($old_sugar_db_version) )
            $GLOBALS['sugar_db_version'] = $old_sugar_db_version;
        if ( isset($old_sugar_version) )
            $GLOBALS['sugar_version'] = $old_sugar_version;
    }

    public function testLoadLanguages()
    {
    	$this->_app->controller->module = 'Contacts';
    	$this->_app->loadLanguages();
    	//since there is a logged in user, the welcome screen should not be empty
    	$this->assertEmpty($GLOBALS['app_strings']['NTC_WELCOME'], 'Testing that Welcome message is not empty');
    	$this->assertNotEmpty($GLOBALS['app_strings'], "App Strings is not empty.");
    	$this->assertNotEmpty($GLOBALS['app_list_strings'], "App List Strings is not empty.");
    	$this->assertNotEmpty($GLOBALS['mod_strings'], "Mod Strings is not empty.");
    }
}
