<?php
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
if (empty($argv[0]) || basename($argv[0]) != basename(__FILE__)) {
    return;
}

$sapi_type = php_sapi_name();
if (substr($sapi_type, 0, 3) != 'cli') {
    die("This is command-line only script");
}

if (empty($argv[1])) {
    die("Use $argv[0] name.zip");
}

$name = $argv[1];

chdir(dirname(__FILE__)."/../..");
$files=array(
    // misc
    'include/SugarSystemInfo/SugarSystemInfo.php',
    'include/SugarHeartbeat/SugarHeartbeatClient.php',

    // healtcheck module
    'modules/HealthCheck/language/en_us.lang.php',
    'modules/HealthCheck/Scanner/Scanner.php',
    'modules/HealthCheck/Scanner/ScannerMeta.php',
    'modules/HealthCheck/Scanner/ScannerWeb.php',
    'modules/HealthCheck/Scanner/ScannerCli.php',
    'modules/HealthCheck/static/css.css',
    'styleguide/assets/fonts/fontawesome-webfont.eot',
    'styleguide/assets/fonts/fontawesome-webfont.svg',
    'styleguide/assets/fonts/fontawesome-webfont.ttf',
    'styleguide/assets/fonts/fontawesome-webfont.woff',
    'styleguide/assets/fonts/FontAwesome.otf',
    'modules/HealthCheck/tpls/index.tpl',
    'modules/HealthCheck/views/view.index.php',
    'modules/HealthCheck/controller.php',
    'modules/HealthCheck/HealthCheck.php',
    'modules/HealthCheck/HealthCheckClient.php',
    'modules/HealthCheck/vardefs.php',
);

$manifest = array(
    'acceptable_sugar_versions' =>
        array (
            'regex_matches' => array('6\.[5-7]\.*','7\.[01]\.*')
        ),
    'author' => 'SugarCRM, Inc.',
    'description' => 'Health Check is a tool that verifies if the instance can be upgraded to version 7.*',
    'icon' => '',
    'is_uninstallable' => 'true',
    'name' => 'Health Check',
    'published_date' => date("Y-m-d H:i:s"),
    'type' => 'module',
);
if (file_exists("modules/UpgradeWizard/upgrader_version.json")) {
    $v = json_decode(file_get_contents('modules/UpgradeWizard/upgrader_version.json'), true);
    if (!empty($v['upgrader_version'])) {
        $manifest['version'] = $v['upgrader_version'];
    }
}
$installdefs = array("id" => "upgrader".time(), "copy" => array());
$zip = new ZipArchive();
$zip->open($name, ZipArchive::CREATE);

foreach ($files as $file) {
    $zip->addFile($file);
    $installdefs['copy'][] = array("from" => "<basepath>/$file", "to" => $file);
}

// register HealthCheck bean
$installdefs['beans'] = array(
    array(
        'module' => 'HealthCheck',
        'class' => 'HealthCheck',
        'path' => 'modules/HealthCheck/HealthCheck.php',
        'tab' => false,
    ),
);

// administration menu entry
$installdefs['copy'][] = array("from" => "<basepath>/healthcheck.php", "to" => "custom/Extension/modules/Administration/Ext/Administration/healthcheck.php");
$zip->addFromString("healthcheck.php", "<?php\n\$admin_group_header[2][3]['Administration']['health_check']= array('HealthCheck','LBL_HEALTH_CHECK_TITLE','LBL_HEALTH_CHECK','./index.php?module=HealthCheck');");

$installdefs['copy'][] = array("from" => "<basepath>/en_us.HealthCheck.php", "to" => "custom/Extension/application/Ext/Language/en_us.HealthCheck.php");
$zip->addFromString("en_us.HealthCheck.php", "<?php\n\$app_strings['LBL_HEALTH_CHECK_TITLE'] = 'Health Check';\$app_strings['LBL_HEALTH_CHECK'] = 'A tool that checks if the system is upgradable.';");

$cont = sprintf("<?php\n\$manifest = %s;\n\$installdefs = %s;\n", var_export($manifest, true), var_export($installdefs, true));
$zip->addFromString("manifest.php", $cont);

$zip->close();
exit(0);
