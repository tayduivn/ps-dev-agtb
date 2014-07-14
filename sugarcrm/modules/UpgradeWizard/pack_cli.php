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
if(empty($argv[0]) || basename($argv[0]) != basename(__FILE__)) return;

$sapi_type = php_sapi_name();
if (substr($sapi_type, 0, 3) != 'cli') {
    die("This is command-line only script");
}

if(empty($argv[1])) {
    die("Use $argv[0] name.zip");
}

$name = $argv[1];

chdir(dirname(__FILE__)."/../..");
$files=array(
    "modules/UpgradeWizard/UpgradeDriver.php",
    "modules/UpgradeWizard/CliUpgrader.php",
    'modules/HealthCheck/Scanner/Scanner.php',
    'modules/HealthCheck/Scanner/ScannerMeta.php',
    'modules/HealthCheck/language/en_us.lang.php',
);

$zip = new ZipArchive();
$zip->open($name, ZipArchive::CREATE);

foreach($files as $file) {
    $zip->addFile($file);
}

$zip->close();
exit(0);
