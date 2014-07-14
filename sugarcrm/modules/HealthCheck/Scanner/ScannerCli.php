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

require_once __DIR__ . '/Scanner.php';

/**
 *
 * HealthCheck Scanner CLI support
 *
 */
class HealthCheckScannerCli extends HealthCheckScanner
{
    /**
     *
     * @param array $argv
     */
    public function parseCliArgs($argv)
    {
        for ($i = 1; $i < (count($argv) - 1); $i++) {

            // logfile name
            if ($argv[$i] == '-l') {
                $i++;
                $this->logfile = $argv[$i];
            }

            // verbose level 1
            if ($argv[$i] == '-v') {
                $this->verbose = 1;
            }

            // verbose level 2 (curently not used)
            if ($argv[$i] == '-vv') {
                $this->verbose = 2;
            }

            // max field count
            if ($argv[$i] == '-m') {
                $i++;
                $this->fieldCountMax = $argv[$i];
            }

            // warn field count
            if ($argv[$i] == '-w') {
                $i++;
                $this->fieldCountWarn = $argv[$i];
            }
        }

        // instance directory
        $this->instance = $argv[count($argv)-1];
    }

    /**
     *
     * Console output - temp solution, need proper central logging
     * @see Scanner::fail()
     */
    public function fail($msg)
    {
        $result = parent::fail($msg);
        echo "$msg\n";
        return $result;
    }

    /**
     *
     * Console output - temp solution, need proper central logging
     * @see Scanner::log()
     */
    protected function log($msg, $tag = 'INFO')
    {
        $fmsg = parent::log($msg, $tag);
        if ($this->verbose) {
            echo $fmsg;
        }
    }
}

/**
 *
 * Standalone CLI HealthCheck runner
 *
 */

if (empty($argv) || empty($argc) || $argc < 2) {
    die("Use php ScannerCli.php [-m maxfieldstoerror] [-w count maxfieldstowarn] [-l logfile] [-v] /path/to/instance\n");
}

$sapi_type = php_sapi_name();
if (substr($sapi_type, 0, 3) != 'cli') {
    die("This is a command-line only script");
}

$scanner = new HealthCheckScannerCli();
$scanner->parseCliArgs($argv);
$scanner->scan();

exit($scanner->getResultCode());
