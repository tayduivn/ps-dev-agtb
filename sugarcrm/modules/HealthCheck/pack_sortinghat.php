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

/**
 *
 * Pack standalone CLI HealthCheck Scanner for OnDemand. This is the same
 * as the previous SortingHat CLI script and can be executed as follows:
 *
 * php ScannerCli.php (for options see ScannerCli.php)
 *
 */

if (empty($argv[0]) || basename($argv[0]) != basename(__FILE__)) {
    return;
}

$sapi_type = php_sapi_name();
if (substr($sapi_type, 0, 3) != 'cli') {
    die("This is a command-line only script\n");
}

if (empty($argv[1])) {
    die("Use $argv[0] healthcheck.phar\n");
}

$zipFile = $argv[1];

$files = array(
    'Scanner/Scanner.php',
    'Scanner/ScannerCli.php',
    'Scanner/ScannerMeta.php',
    'language/en_us.lang.php',
);

$phar = new Phar($argv[1]);

foreach ($files as $file) {
    $phar->addFile($file, $file);
}

$stub = <<<'STUB'
<?php 
Phar::mapPhar();
set_include_path('phar://' . __FILE__ . PATH_SEPARATOR . get_include_path());
require_once "Scanner/ScannerCli.php";
if (empty($argv) || empty($argc) || $argc < 2) {
    die("Use php {$argv[0]} [-d property1=value1... property1=valueN] [-l logfile] [-v] /path/to/instance\n");
}

$sapi_type = php_sapi_name();
if (substr($sapi_type, 0, 3) != 'cli') {
    die("This is a command-line only script");
}

$scanner = new HealthCheckScannerCli();
$scanner->parseCliArgs($argv);
$scanner->scan();

exit($scanner->getResultCode());
__HALT_COMPILER();
STUB;
$phar->setStub($stub);

exit(0);
