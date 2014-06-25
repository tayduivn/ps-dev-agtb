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
 * Standalone CLI HealthCheck runner
 *
 */

if (empty($argv) || empty($argc) || $argc < 2) {
    die("Use php scan.php [-l logfile] [-v] /path/to/instance\n");
}

$sapi_type = php_sapi_name();
if (substr($sapi_type, 0, 3) != 'cli') {
    die("This is a command-line only script");
}

require_once __DIR__ . '/Scanner/ScannerCli.php';

$scanner = new ScannerCli();
$scanner->parseCliArgs($argv);
$scanner->scan();

exit($scanner->getResultCode());
