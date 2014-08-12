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
    die("Use $argv[0] name (no zip or phar extension)\n");
}

$pathinfo = pathinfo($argv[1]);

$name = $pathinfo['dirname'] . DIRECTORY_SEPARATOR . $pathinfo['filename'];

chdir(dirname(__FILE__)."/../..");
$files=array(
    "modules/UpgradeWizard/SILENTUPGRADE.txt" => 'SILENTUPGRADE.txt',
    "modules/UpgradeWizard/UpgradeDriver.php" => 'UpgradeDriver.php',
    "modules/UpgradeWizard/CliUpgrader.php" => 'CliUpgrader.php',
    "modules/UpgradeWizard/upgrader_version.json" => 'upgrader_version.json',
    'modules/HealthCheck/Scanner/Scanner.php' => 'Scanner/Scanner.php',
    'modules/HealthCheck/Scanner/ScannerCli.php' => 'Scanner/ScannerCli.php',
    'modules/HealthCheck/Scanner/ScannerMeta.php' => 'Scanner/ScannerMeta.php',
    'modules/HealthCheck/language/en_us.lang.php' => 'language/en_us.lang.php'
);

$phar = new Phar($name . '.phar');

foreach ($files as $file => $inArchive) {
    $phar->addFile($file, $inArchive);
}

$stub = <<<'STUB'
<?php
Phar::mapPhar();
set_include_path('phar://' . __FILE__ . PATH_SEPARATOR . get_include_path());
require_once "CliUpgrader.php"; CliUpgrader::start(); __HALT_COMPILER();
STUB;
$phar->setStub($stub);

$zip = new ZipArchive();
$zip->open($name . '.zip', ZipArchive::CREATE);

foreach ($files as $file => $local) {
    $zip->addFile($file, $local);
}

$zip->close();

exit(0);
