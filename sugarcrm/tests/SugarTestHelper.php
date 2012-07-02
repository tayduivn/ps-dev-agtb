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

if(!defined('sugarEntry')) define('sugarEntry', true);

set_include_path(
    dirname(__FILE__) . PATH_SEPARATOR .
    dirname(__FILE__) . '/..' . PATH_SEPARATOR .
    get_include_path()
);

// constant to indicate that we are running tests
if (!defined('SUGAR_PHPUNIT_RUNNER'))
    define('SUGAR_PHPUNIT_RUNNER', true);

// initialize the various globals we use
global $sugar_config, $db, $fileName, $current_user, $locale, $current_language;

if ( !isset($_SERVER['HTTP_USER_AGENT']) )
    // we are probably running tests from the command line
    $_SERVER['HTTP_USER_AGENT'] = 'cli';

// move current working directory
chdir(dirname(__FILE__) . '/..');

require_once('include/entryPoint.php');

require_once('include/utils/layout_utils.php');

$GLOBALS['db'] = DBManagerFactory::getInstance();

$current_language = $sugar_config['default_language'];
// disable the SugarLogger
$sugar_config['logger']['level'] = 'fatal';

$GLOBALS['sugar_config']['default_permissions'] = array (
		'dir_mode' => 02770,
		'file_mode' => 0777,
		'chown' => '',
		'chgrp' => '',
	);

$GLOBALS['js_version_key'] = 'testrunner';

if ( !isset($_SERVER['SERVER_SOFTWARE']) )
    $_SERVER["SERVER_SOFTWARE"] = 'PHPUnit';

// helps silence the license checking when running unit tests.
$_SESSION['VALIDATION_EXPIRES_IN'] = 'valid';

$GLOBALS['startTime'] = microtime(true);

// clean out the cache directory
require_once('modules/Administration/QuickRepairAndRebuild.php');
$repair = new RepairAndClear();
$repair->module_list = array();
$repair->show_output = false;
$repair->clearJsLangFiles();
$repair->clearJsFiles();

//BEGIN SUGARCRM flav=pro ONLY
// make sure the client license has been validated
$license = new Administration();
$license = $license->retrieveSettings('license', true);
if ( !isset($license->settings['license_vk_end_date']))
    $license->saveSetting('license', 'vk_end_date', date('Y-m-d',strtotime('+1 year')));
//END SUGARCRM flav=pro ONLY
// mark that we got by the admin wizard already
$focus = new Administration();
$focus->retrieveSettings();
$focus->saveSetting('system','adminwizard',1);

// include the other test tools
require_once 'SugarTestObjectUtilities.php';
require_once 'SugarTestProjectUtilities.php';
require_once 'SugarTestProjectTaskUtilities.php';
require_once 'SugarTestUserUtilities.php';
require_once 'SugarTestLangPackCreator.php';
require_once 'SugarTestThemeUtilities.php';
require_once 'SugarTestContactUtilities.php';
require_once 'SugarTestEmailUtilities.php';
require_once 'SugarTestCampaignUtilities.php';
require_once 'SugarTestLeadUtilities.php';
require_once 'SugarTestStudioUtilities.php';
require_once 'SugarTestMeetingUtilities.php';
require_once 'SugarTestCallUtilities.php';
require_once 'SugarTestAccountUtilities.php';
require_once 'SugarTestTrackerUtility.php';
require_once 'SugarTestImportUtilities.php';
require_once 'SugarTestMergeUtilities.php';
require_once 'SugarTestTaskUtilities.php';
require_once 'SugarTestQuotaUtilities.php';
//BEGIN SUGARCRM flav=pro ONLY
require_once 'SugarTestTeamUtilities.php';
require_once 'SugarTestQuoteUtilities.php';
require_once 'SugarTestProductUtilities.php';
require_once 'SugarTestProductCategoryUtilities.php';
require_once 'SugarTestProductTypeUtilities.php';
require_once 'SugarTestProductBundleUtilities.php';
require_once 'SugarTestOpportunityUtilities.php';
require_once 'SugarTestWorksheetUtilities.php';
//END SUGARCRM flav=pro ONLY

$GLOBALS['db']->commit();

// define our testcase subclass
class Sugar_PHPUnit_Framework_TestCase extends PHPUnit_Framework_TestCase
{
    protected $backupGlobals = FALSE;

    protected $useOutputBuffering = true;

    protected function assertPreConditions()
    {
        if(isset($GLOBALS['log'])) {
            $GLOBALS['log']->info("START TEST: {$this->getName(false)}");
        }
        SugarCache::instance()->flush();
    }

    protected function assertPostConditions() {
        if(!empty($_REQUEST)) {
            foreach(array_keys($_REQUEST) as $k) {
		        unset($_REQUEST[$k]);
		    }
        }

        if(!empty($_POST)) {
            foreach(array_keys($_POST) as $k) {
		        unset($_POST[$k]);
		    }
        }

        if(!empty($_GET)) {
            foreach(array_keys($_GET) as $k) {
		        unset($_GET[$k]);
		    }
        }
        if(isset($GLOBALS['log'])) {
            $GLOBALS['log']->info("DONE TEST: {$this->getName(false)}");
        }
        // reset error handler in case somebody set it
        restore_error_handler();
    }

    public static function tearDownAfterClass()
    {
        unset($GLOBALS['disable_date_format']);
        unset($GLOBALS['saving_relationships']);
        unset($GLOBALS['updating_relationships']);
        $GLOBALS['timedate']->clearCache();
    }
}

// define output testcase subclass
class Sugar_PHPUnit_Framework_OutputTestCase extends PHPUnit_Extensions_OutputTestCase
{
    protected $backupGlobals = FALSE;

    protected $_notRegex;
    protected $_outputCheck;

    protected function assertPreConditions()
    {
        if(isset($GLOBALS['log'])) {
            $GLOBALS['log']->info("START TEST: {$this->getName(false)}");
        }
        SugarCache::instance()->flush();
    }

    protected function assertPostConditions() {
        if(!empty($_REQUEST)) {
            foreach(array_keys($_REQUEST) as $k) {
		        unset($_REQUEST[$k]);
		    }
        }

        if(!empty($_POST)) {
            foreach(array_keys($_POST) as $k) {
		        unset($_POST[$k]);
		    }
        }

        if(!empty($_GET)) {
            foreach(array_keys($_GET) as $k) {
		        unset($_GET[$k]);
		    }
        }
        if(isset($GLOBALS['log'])) {
            $GLOBALS['log']->info("DONE TEST: {$this->getName(false)}");
        }
    }

    protected function NotRegexCallback($output)
    {
        if(empty($this->_notRegex)) {
            return true;
        }
        $this->assertNotRegExp($this->_notRegex, $output);
        return true;
    }

    public function setOutputCheck($callback)
    {
        if (!is_callable($callback)) {
            throw new PHPUnit_Framework_Exception;
        }

        $this->_outputCheck = $callback;
    }

    protected function runTest()
    {
		$testResult = parent::runTest();
        if($this->_outputCheck) {
            $this->assertTrue(call_user_func($this->_outputCheck, $this->output));
        }
        return $testResult;
    }

    public function expectOutputNotRegex($expectedRegex)
    {
        if (is_string($expectedRegex) || is_null($expectedRegex)) {
            $this->_notRegex = $expectedRegex;
        }

        $this->setOutputCheck(array($this, "NotRegexCallback"));
    }

}

// define a mock logger interface; used for capturing logging messages emited
// the test suite
class SugarMockLogger
{
	private $_messages = array();

	public function __call($method, $message)
	{
		$this->messages[] = strtoupper($method) . ': ' . $message[0];
	}

	public function getLastMessage()
	{
		return end($this->messages);
	}

	public function getMessageCount()
	{
		return count($this->messages);
	}
}
