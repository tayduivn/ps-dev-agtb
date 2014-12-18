<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

global $sugar_config, $db, $app_strings, $mod_strings;

if (isset($sugar_config['default_language']) == false) {
    $sugar_config['default_language'] = $GLOBALS['current_language'];
}
$app_strings = return_application_language($GLOBALS['current_language']);

if (!isset($install_script) || !$install_script) {
    die($mod_strings['ERR_NO_DIRECT_SCRIPT']);
}

$db = getDbConnection();
//BEGIN SUGARCRM lic=sub ONLY
if (isset($_SESSION['licenseKey_submitted']) && ($_SESSION['licenseKey_submitted']) && isset($_SESSION['setup_db_type'])) {
    if (isset($GLOBALS['license']) && isset($GLOBALS['license']->settings)) {
        if (isset($GLOBALS['license']->settings['license_users'])) {
            $_SESSION['setup_license_key_users'] = $GLOBALS['license']->settings['license_users'];
        }
        if (isset($GLOBALS['license']->settings['license_expire_date'])) {
            $_SESSION['setup_license_key_expire_date'] = $GLOBALS['license']->settings['license_expire_date'];
        }
        if (isset($GLOBALS['license']->settings['license_num_lic_oc'])) {
            $_SESSION['setup_num_lic_oc'] = $GLOBALS['license']->settings['license_num_lic_oc'];
        }
    }
}
//BEGIN SUGARCRM lic=sub ONLY
$dbCreate = "({$mod_strings['LBL_CONFIRM_WILL']} ";
if (!$_SESSION['setup_db_create_database']) {
    $dbCreate .= $mod_strings['LBL_CONFIRM_NOT'];
}
$dbCreate .= " {$mod_strings['LBL_CONFIRM_BE_CREATED']})";

$dbUser = "{$_SESSION['setup_db_sugarsales_user']} ({$mod_strings['LBL_CONFIRM_WILL']} ";
if ($_SESSION['setup_db_create_sugarsales_user'] != 1) {
    $dbUser .= $mod_strings['LBL_CONFIRM_NOT'];
}
$dbUser .= " {$mod_strings['LBL_CONFIRM_BE_CREATED']})";
$yesNoDropCreate = $mod_strings['LBL_NO'];
if ($_SESSION['setup_db_drop_tables'] === true || $_SESSION['setup_db_drop_tables'] == 'true') {
    $yesNoDropCreate = $mod_strings['LBL_YES'];
}
$yesNoSugarUpdates = ($_SESSION['setup_site_sugarbeet']) ? $mod_strings['LBL_YES'] : $mod_strings['LBL_NO'];
$yesNoCustomSession = ($_SESSION['setup_site_custom_session_path']) ? $mod_strings['LBL_YES'] : $mod_strings['LBL_NO'];
$yesNoCustomLog = ($_SESSION['setup_site_custom_log_dir']) ? $mod_strings['LBL_YES'] : $mod_strings['LBL_NO'];
$yesNoCustomId = ($_SESSION['setup_site_specify_guid']) ? $mod_strings['LBL_YES'] : $mod_strings['LBL_NO'];
$demoData = ($sugar_config['default_language'] == 'en_us') ? ($mod_strings['LBL_YES']) : ($_SESSION['demoData']);

// Populate the default date format, time format, and language for the system
$defaultDateFormat = "";
$defaultTimeFormat = "";
$defaultLanguages = "";

$sugar_config_defaults = get_sugar_config_defaults();
if (isset($_REQUEST['default_language'])) {
    $defaultLanguages = $sugar_config_defaults['languages'][$_REQUEST['default_language']];
}

$langHeader = get_language_header();

// CONFIGURATION SETTINGS

// mbstring.func_overload
$mbStatus = $mod_strings['LBL_CHECKSYS_OK'];
$mb = ini_get('mbstring.func_overload');
if ($mb > 1) {
    $mbStatus = '<b><span class="stop">' . translate('ERR_UW_MBSTRING_FUNC_OVERLOAD', 'UpgradeWizard') . '</span></b>';
    $ret['error_found'] = true;
}

$error_found = true;

// memory limit
$memory_msg = "";

// CL - fix for 9183 (if memory_limit is enabled we will honor it and check it; otherwise use unlimited)
$memory_limit = ini_get('memory_limit');
if (empty($memory_limit)) {
    $memory_limit = "-1";
}
if (!defined('SUGARCRM_MIN_MEM')) {
    define('SUGARCRM_MIN_MEM', 40 * 1024 * 1024);
}
$sugarMinMem = constant('SUGARCRM_MIN_MEM');
// logic based on: http://us2.php.net/manual/en/ini.core.php#ini.memory-limit
if ($memory_limit == "") {          // memory_limit disabled at compile time, no memory limit
    $memory_msg = "<b>{$mod_strings['LBL_CHECKSYS_MEM_OK']}</b>";
} elseif ($memory_limit == "-1") {   // memory_limit enabled, but set to unlimited
    $memory_msg = "{$mod_strings['LBL_CHECKSYS_MEM_UNLIMITED']}";
} else {
    $mem_display = $memory_limit;
    preg_match('/^\s*([0-9.]+)\s*([KMGTPE])B?\s*$/i', $memory_limit, $matches);
    $num = (float)$matches[1];
    // Don't break so that it falls through to the next case.
    switch (strtoupper($matches[2])) {
        case 'G':
            $num = $num * 1024;
        case 'M':
            $num = $num * 1024;
        case 'K':
            $num = $num * 1024;
    }
    $memory_limit_int = intval($num);
    $SUGARCRM_MIN_MEM = (int)constant('SUGARCRM_MIN_MEM');
    if ($memory_limit_int < constant('SUGARCRM_MIN_MEM')) {
        $memory_msg = "<span class='stop'><b>$memory_limit{$mod_strings['ERR_CHECKSYS_MEM_LIMIT_1']}" . constant('SUGARCRM_MIN_MEM') . "{$mod_strings['ERR_CHECKSYS_MEM_LIMIT_2']}</b></span>";
        $memory_msg = str_replace('$memory_limit', $mem_display, $memory_msg);
    } else {
        $memory_msg = "{$mod_strings['LBL_CHECKSYS_OK']} ({$memory_limit})";
    }
}

// zlib
if (function_exists('gzclose')) {
    $zlibStatus = "{$mod_strings['LBL_CHECKSYS_OK']}";
} else {
    $zlibStatus = "<span class='stop'><b>{$mod_strings['ERR_CHECKSYS_ZLIB']}</b></span>";
}

// zip
if (class_exists("ZipArchive")) {
    $zipStatus = "{$mod_strings['LBL_CHECKSYS_OK']}";
} else {
    $zipStatus = "<span class='stop'><b>{$mod_strings['ERR_CHECKSYS_ZIP']}</b></span>";
}

// imap
if (function_exists('imap_open')) {
    $imapStatus = "{$mod_strings['LBL_CHECKSYS_OK']}";
} else {
    $imapStatus = "<span class='stop'><b>{$mod_strings['ERR_CHECKSYS_IMAP']}</b></span>";
}

// cURL
if (function_exists('curl_init')) {
    $curlStatus = "{$mod_strings['LBL_CHECKSYS_OK']}";
} else {
    $curlStatus = "<span class='stop'><b>{$mod_strings['ERR_CHECKSYS_CURL']}</b></span>";
}

//CHECK UPLOAD FILE SIZE
$upload_max_filesize = ini_get('upload_max_filesize');
$upload_max_filesize_bytes = return_bytes($upload_max_filesize);
if (!defined('SUGARCRM_MIN_UPLOAD_MAX_FILESIZE_BYTES')) {
    define('SUGARCRM_MIN_UPLOAD_MAX_FILESIZE_BYTES', 6 * 1024 * 1024);
}

if ($upload_max_filesize_bytes > constant('SUGARCRM_MIN_UPLOAD_MAX_FILESIZE_BYTES')) {
    $fileMaxStatus = "{$mod_strings['LBL_CHECKSYS_OK']}</font>";
} else {
    $fileMaxStatus = "<span class='stop'><b>{$mod_strings['ERR_UPLOAD_MAX_FILESIZE']}</font></b></span>";
}

//CHECK Sprite support
if (function_exists('imagecreatetruecolor')) {
    $spriteSupportStatus = "{$mod_strings['LBL_CHECKSYS_OK']}</font>";
} else {
    $spriteSupportStatus = "<span class='stop'><b>{$mod_strings['ERROR_SPRITE_SUPPORT']}</b></span>";
}

// Suhosin allow to use upload://
if (UploadStream::getSuhosinStatus() == true || (strpos(ini_get('suhosin.perdir'), 'e') !== false && strpos($_SERVER["SERVER_SOFTWARE"], 'Microsoft-IIS') === false)) {
    $suhosinStatus = "{$mod_strings['LBL_CHECKSYS_OK']}";
} else {
    $suhosinStatus = "<span class='stop'><b>{$app_strings['ERR_SUHOSIN']}</b></span>";
}
$uploadStream = UploadStream::STREAM_NAME;

// PHP.ini
$phpIniLocation = get_cfg_var("cfg_file_path");

// CRON Settings
if (!isset($sugar_config['default_language']))
    $sugar_config['default_language'] = $_SESSION['default_language'];
if (!isset($sugar_config['cache_dir']))
    $sugar_config['cache_dir'] = $sugar_config_defaults['cache_dir'];
if (!isset($sugar_config['site_url']))
    $sugar_config['site_url'] = $_SESSION['setup_site_url'];
if (!isset($sugar_config['translation_string_prefix']))
    $sugar_config['translation_string_prefix'] = $sugar_config_defaults['translation_string_prefix'];
$mod_strings_scheduler = return_module_language($GLOBALS['current_language'], 'Schedulers');
$error = '';

if (!isset($_SERVER['PATH'])) {
    $_SERVER['Path'] = getenv('Path');
}

$sugar_smarty = new Sugar_Smarty();

$sugar_smarty->assign('icon', $icon);
$sugar_smarty->assign('css', $css);
$sugar_smarty->assign('loginImage', $loginImage);

$sugar_smarty->assign('help_url', $help_url);
$sugar_smarty->assign('sugar_md', $sugar_md);
$sugar_smarty->assign('langHeader', get_language_header());
$sugar_smarty->assign('versionToken', getVersionedPath(null));
$sugar_smarty->assign('next_step', $next_step);

$sugar_smarty->assign('APP', $app_strings);
$sugar_smarty->assign('MOD', $mod_strings);


$sugar_smarty->assign('db', $db);
$sugar_smarty->assign('is_windows', is_windows());
$sugar_smarty->assign('mod_strings_scheduler', $mod_strings_scheduler);

$sugar_smarty->assign('dbCreate', $dbCreate);
$sugar_smarty->assign('yesNoDropCreate', $yesNoDropCreate);
$sugar_smarty->assign('yesNoSugarUpdates', $yesNoSugarUpdates);
$sugar_smarty->assign('yesNoCustomSession', $yesNoCustomSession);
$sugar_smarty->assign('yesNoCustomLog', $yesNoCustomLog);
$sugar_smarty->assign('yesNoCustomId', $yesNoCustomId);
$sugar_smarty->assign('demoData', $demoData);

$sugar_smarty->assign('mbStatus', $mbStatus);
$sugar_smarty->assign('memory_msg', $memory_msg);
$sugar_smarty->assign('zlibStatus', $zlibStatus);
$sugar_smarty->assign('zipStatus', $zipStatus);
$sugar_smarty->assign('imapStatus', $imapStatus);
$sugar_smarty->assign('curlStatus', $curlStatus);
$sugar_smarty->assign('fileMaxStatus', $fileMaxStatus);
$sugar_smarty->assign('spriteSupportStatus', $spriteSupportStatus);
$sugar_smarty->assign('suhosinStatus', $suhosinStatus);
$sugar_smarty->assign('uploadStream', $uploadStream);
$sugar_smarty->assign('phpIniLocation', $phpIniLocation);

$sugar_smarty->display("install/templates/confirmSettings.tpl");
