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

define('sugarEntry', true);

set_include_path(
    dirname(__FILE__) . PATH_SEPARATOR .
    dirname(__FILE__) . '/..' . PATH_SEPARATOR .
    dirname(__FILE__) . '/../..' . PATH_SEPARATOR .
    get_include_path()
);

// constant to indicate that we are running tests
define('SUGAR_PHPUNIT_RUNNER', true);

// prevent ext/session from trying to send headers, since it doesn't make sense in CLI mode
// and will conflict with PHPUnit output
ini_set('session.use_cookies', false);
session_cache_limiter(false);

// initialize the various globals we use
global $sugar_config, $db, $fileName, $current_user, $locale, $current_language;
if (!isset($_SERVER['HTTP_USER_AGENT'])) {
    // we are probably running tests from the command line
    $_SERVER['HTTP_USER_AGENT'] = 'cli';
}

if (!isset($_SERVER['SERVER_SOFTWARE'])) {
    $_SERVER['SERVER_SOFTWARE'] = 'PHPUnit';
}

// move current working directory
chdir(__DIR__ . '/../..');

// this is needed so modules.php properly registers the modules globals, otherwise they
// end up defined in wrong scope
global $beanFiles, $beanList, $objectList, $moduleList, $modInvisList, $bwcModules, $sugar_version, $sugar_flavor;
require_once 'include/entryPoint.php';
require_once 'include/utils/layout_utils.php';
require_once 'modules/DynamicFields/FieldCases.php';

$GLOBALS['db'] = DBManagerFactory::getInstance();

$current_language = $sugar_config['default_language'];
// disable the SugarLogger
$sugar_config['logger']['level'] = 'fatal';

$GLOBALS['sugar_config']['default_permissions'] = array(
    'dir_mode' => 02770,
    'file_mode' => 0777,
    'chown' => '',
    'chgrp' => '',
);

$GLOBALS['js_version_key'] = 'testrunner';

// helps silence the license checking when running unit tests.
$_SESSION['VALIDATION_EXPIRES_IN'] = 'valid';

$GLOBALS['startTime'] = microtime(true);

// clean out the cache directory
$repair = new RepairAndClear();
$repair->module_list = array();
$repair->show_output = false;
$repair->clearJsLangFiles();
$repair->clearJsFiles();

// make sure the client license has been validated
$license = new Administration();
$license = $license->retrieveSettings('license', true);
if (!isset($license->settings['license_vk_end_date'])) {
    $license->saveSetting('license', 'vk_end_date', date('Y-m-d', strtotime('+1 year')));
}

// mark that we got by the admin wizard already
$focus = new Administration();
$focus->retrieveSettings();
$focus->saveSetting('system', 'adminwizard', 1);

// custom helper support
if (file_exists('custom/tests/SugarTestHelperInclude.php')) {
    require_once 'custom/tests/SugarTestHelperInclude.php';
}

$GLOBALS['db']->commit();

// define our testcase subclass
if (function_exists('shadow_get_config') && ($sc = shadow_get_config()) != false && !empty($sc['template'])) {
    // shadow is enabled
    define('SHADOW_ENABLED', true);
    define('SHADOW_CHECK', false); // disable for faster tests
} else {
    define('SHADOW_ENABLED', false);
    define('SHADOW_CHECK', false);
}

// Disables sending email.
define('DISABLE_EMAIL_SEND', true);
