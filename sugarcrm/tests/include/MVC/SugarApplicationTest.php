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
        $this->_app = new SugarApplicationMock();
        if ( isset($_SESSION['authenticated_user_theme']) )
            unset($_SESSION['authenticated_user_theme']);

        if ( isset($GLOBALS['sugar_config']['http_referer']) ) {
            $this->prevRefererList = $GLOBALS['sugar_config']['http_referer'];
        }

        $GLOBALS['sugar_config']['http_referer'] = array('list' => array(), 'actions' => array());
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
        /*
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
        */
        $GLOBALS['current_language'] = $GLOBALS['sugar_config']['default_language'];

        if ( isset($this->prevRefererList)) {
            $GLOBALS['sugar_config']['http_referer'] = $this->prevRefererList;
        } else {
            unset ($GLOBALS['sugar_config']['http_referer']);
        }
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
        $_REQUEST['usertheme'] = (string) SugarThemeRegistry::getDefault();

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

    public function testCheckHTTPRefererReturnsTrueIfRefererNotSet()
    {
        $_SERVER['HTTP_REFERER'] = '';
        $_SERVER['SERVER_NAME'] = 'dog';
        $this->_app->controller->action = 'index';

        $this->assertTrue($this->_app->checkHTTPReferer());
    }

    /**
     * @ticket 39691
     */
    public function testCheckHTTPRefererReturnsTrueIfRefererIsLocalhost()
    {
        $_SERVER['HTTP_REFERER'] = 'http://localhost';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $this->_app->controller->action = 'poo';

        $this->assertTrue($this->_app->checkHTTPReferer());

        $_SERVER['HTTP_REFERER'] = 'http://127.0.0.1';
        $_SERVER['SERVER_NAME'] = 'localhost';
        $this->_app->controller->action = 'poo';

        $this->assertTrue($this->_app->checkHTTPReferer());

        $_SERVER['HTTP_REFERER'] = 'http://localhost';
        $_SERVER['SERVER_NAME'] = '127.0.0.1';
        $this->_app->controller->action = 'poo';

        $this->assertTrue($this->_app->checkHTTPReferer());

        $_SERVER['HTTP_REFERER'] = 'http://127.0.0.1';
        $_SERVER['SERVER_NAME'] = '127.0.0.1';
        $this->_app->controller->action = 'poo';

        $this->assertTrue($this->_app->checkHTTPReferer());
    }

    public function testCheckHTTPRefererReturnsTrueIfRefererIsServerName()
    {
        $_SERVER['HTTP_REFERER'] = 'http://dog';
        $_SERVER['SERVER_NAME'] = 'dog';
        $this->_app->controller->action = 'index';

        $this->assertTrue($this->_app->checkHTTPReferer());
    }

    public function testCheckHTTPRefererReturnsTrueIfRefererIsInWhitelist()
    {
        $_SERVER['HTTP_REFERER'] = 'http://dog';
        $_SERVER['SERVER_NAME'] = 'cat';
        $this->_app->controller->action = 'index';

        $GLOBALS['sugar_config']['http_referer']['list'][] = 'http://dog';

        $this->assertTrue($this->_app->checkHTTPReferer());
    }

    public function testCheckHTTPRefererReturnsFalseIfRefererIsNotInWhitelist()
    {
        $_SERVER['HTTP_REFERER'] = 'http://dog';
        $_SERVER['SERVER_NAME'] = 'cat';
        $this->_app->controller->action = 'poo';

        $GLOBALS['sugar_config']['http_referer']['list'] = array();

        $this->assertFalse($this->_app->checkHTTPReferer());
    }

    public function testCheckHTTPRefererReturnsTrueIfRefererIsNotInWhitelistButActionIs()
    {
        $_SERVER['HTTP_REFERER'] = 'http://dog';
        $_SERVER['SERVER_NAME'] = 'cat';
        $this->_app->controller->action = 'index';

        $this->assertTrue($this->_app->checkHTTPReferer());
    }

    public function testCheckHTTPRefererReturnsTrueIfRefererIsNotInWhitelistButActionIsInConfig()
    {
        $_SERVER['HTTP_REFERER'] = 'http://dog';
        $_SERVER['SERVER_NAME'] = 'cat';
        $this->_app->controller->action = 'poo';

        $GLOBALS['sugar_config']['http_referer']['actions'][] = 'poo';
        $this->assertTrue($this->_app->checkHTTPReferer());
    }

    /**
     * @bug 50302
     */
    public function testWhitelistDefaults()
    {
        $_SERVER['HTTP_REFERER'] = 'http://dog';
        $_SERVER['SERVER_NAME'] = 'cat';
        $GLOBALS['sugar_config']['http_referer']['actions'] = array('poo');
        $this->_app->controller->action = 'oauth';
        $this->assertTrue($this->_app->checkHTTPReferer());
        $this->_app->controller->action = 'index';
        $this->assertTrue($this->_app->checkHTTPReferer());
        $this->_app->controller->action = 'save';
        $this->assertFalse($this->_app->checkHTTPReferer());
    }

    /**
     * @group Login
     */
    public function testGetAuthenticatedUrl_DefaultShouldBeSidecar()
    {
        $appReflection = new ReflectionClass("SugarApplication");
        $method = $appReflection->getMethod('getAuthenticatedHomeUrl');
        $method->setAccessible(true);

        $url = $method->invoke($this->_app);

        $this->assertContains("index.php?action=sidecar#Home", $url);
    }

    /**
     * @group Login
     */
    public function testGetAuthenticatedUrl_AllowsDisablingOfSidecarWithUrlParameter()
    {
        $appReflection = new ReflectionClass("SugarApplication");
        $method = $appReflection->getMethod('getAuthenticatedHomeUrl');
        $method->setAccessible(true);
        
        $_GET['sidecar'] = '0';
        
        $url = $method->invoke($this->_app);

        $this->assertContains("index.php?module=Home&action=index", $url);
    }

    /**
     * @group Login
     */
    public function testGetUnauthenticatedUrl_DefaultShouldBeSidecar()
    {
        $appReflection = new ReflectionClass("SugarApplication");
        $method = $appReflection->getMethod('getUnauthenticatedHomeUrl');
        $method->setAccessible(true);

        $url = $method->invoke($this->_app);

        $this->assertContains("index.php?action=sidecar#Home", $url);
    }


    /**
     * @group Login
     */
    public function testGetUnauthenticatedUrl_AllowsDisablingOfSidecarWithUrlParameter()
    {
        $appReflection = new ReflectionClass("SugarApplication");
        $method = $appReflection->getMethod('getUnauthenticatedHomeUrl');
        $method->setAccessible(true);

        $_GET['sidecar'] = '0';

        $url = $method->invoke($this->_app);

        $this->assertContains("index.php?action=Login&module=Users", $url);
    }
}

class SugarApplicationMock extends SugarApplication
{
    public function __construct()
    {
        parent::SugarApplication();
        $this->controller = new stdClass();
    }

    public function checkHTTPReferer()
	{
	    return parent::checkHTTPReferer(false);
	}
}
