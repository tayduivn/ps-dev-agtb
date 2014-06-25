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
 * Pack standalone CLI HealthCheck Scanner
 * usage: see scan.php
 */

if (empty($argv[0]) || basename($argv[0]) != basename(__FILE__)) {
    return;
}

$sapi_type = php_sapi_name();
if (substr($sapi_type, 0, 3) != 'cli') {
    die("This is a command-line only script\n");
}

if (empty($argv[1])) {
    die("Use pack.php name.zip\n");
}

$zipFile = $argv[1];

$files = array(
    'scan.php',
    'Scanner.php',
    'ScannerCli.php',
    'ScannerMeta.php',
    'language/en_us.lang.php',
);

$zip = new ZipArchive();
$zip->open($zipFile, ZipArchive::OVERWRITE);

$baseDir = 'HealthCheck';
foreach ($files as $file) {
    $zip->addFile($file, "{$baseDir}/{$file}");
}
$zip->close();

exit(0);
