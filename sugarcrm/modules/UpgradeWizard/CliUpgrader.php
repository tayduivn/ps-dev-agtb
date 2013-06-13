<?php
require_once 'UpgradeDriver.php';

/**
 * Command-line upgrader
 */
class CliUpgrader extends UpgradeDriver
{
    /*
     * CLI arguments: Zipfile Logfile Sugardir Adminuser [Stage]
     */
    public function runStage($stage)
    {
        $argv = $this->context['argv'];
        $argv[] = $stage;
        $cmd = "{$this->context['php']} -f {$this->context['script']} " . $this->buildArgString($argv);
        $this->log("Running $cmd");
        passthru($cmd, $retcode);
        return ($retcode == 0);
    }

    protected static function bannerError($msg)
    {
    	echo "*******************************************************************************\n";
    	echo "*** ERROR: $msg\n";
    	echo "FAILURE\n";
    }

    protected static function argError($msg)
    {
        static::bannerError($msg);
        static::usage();
        exit(1);
    }

    protected static function usage()
    {
$usage =<<<eoq2
Usage: php -f CliUpgrader.php upgradeZipFile logFile pathToSugarInstance admin-user

Example:
    [path-to-PHP/]php -f CliUpgrader.php [path-to-upgrade-package/]SugarEnt-Upgrade-6.6.x-to-6.7.0.zip [path-to-log-file/]silentupgrade.log  path-to-sugar-instance/ admin

Arguments:
    upgradeZipFile                       : Upgrade package file.
    logFile                              : Silent Upgarde log file.
    pathToSugarInstance                  : Sugar instance being upgraded.
    admin-user                           : admin user performing the upgrade

eoq2;
        echo $usage;
    }

    protected function verifyArguments($argv)
    {
        if(empty($this->context['source_dir']) || !is_dir($this->context['source_dir'])) {
            self::argError("3rd parameter must be a valid directory: {$argv[3]}.");
        }

        if(!is_file("{$this->context['source_dir']}/include/entryPoint.php") || !is_file("{$this->context['source_dir']}/config.php")) {
            self::argError("{$this->context['source_dir']} is not a SugarCRM directory.");
        }

        if(!is_readable("{$this->context['source_dir']}/include/entryPoint.php") || !is_readable("{$this->context['source_dir']}/config.php")) {
            self::argError("{$this->context['source_dir']} is not a accessible.");
        }

        if(!is_file($this->context['zip'])) { // valid zip?
            self::argError("First argument must be a full path to the patch file: {$argv[1]}.");
        }

        if(!is_readable($this->context['zip'])) { // valid zip?
            self::argError("{$argv[1]} is not readable.");
        }
        return true;
    }

    /**
     * Map CLI arguments into context entries
     * @param array $argv
     * @return array
     */
    public static function mapArgs($argv)
    {
        if(count($argv) < 5) {
            $cnt = count($argv);
            static::argError("Upgrader requires 4 argumens, $cnt given");
            return array(); // never happens
        }

        $context = array(
                'zip' => realpath($argv[1]),
                'log' => $argv[2],
                'source_dir' => realpath($argv[3]),
                'admin' => $argv[4],
        );
        if(isset($argv[5])) {
            $context['stage'] = $argv[5];
        }
        return $context;
    }

    /**
     * Parse CLI arguments into context
     * @param array $argv
     * @return array
     */
    public static function parseArgs($argv)
    {
        if(defined('PHP_BINDIR')) {
        	$php_path = PHP_BINDIR."/";
        } else {
        	$php_path = '';
        }
        if(!file_exists($php_path . 'php')) {
            $php_path = '';
        }
        $context = static::mapArgs($argv);
        $context['php'] = $php_path."php";
        $context['script'] = __FILE__;
        $context['argv'] = $argv;
        return $context;
    }

    /**
     * Execution starts here
     */
    public static function start()
    {
        global $argv;
        $upgrader = new static(static::parseArgs($argv));
        $upgrader->verifyArguments($argv);
        if(isset($upgrader->context['stage'])) {
            $stage = $upgrader->context['stage'];
        } else {
            $stage = null;
        }
        if($stage && $stage != 'continue') {
            // Run one step
            if($upgrader->run($stage)) {
                exit(0);
            } else {
                if(!empty($upgrader->error)) {
                    echo "ERROR: {$upgrader->error}\n";
                }
                exit(1);
            }
        } else {
            // whole loop
            if($stage != 'continue') {
                // reset state
                $upgrader->cleanState();
            }
            while(1) {
                $res = $upgrader->runStep($stage);
                if($res === false) {
                    if($stage) {
                        echo "***************         Step \"{$stage}\" FAILED!\n";
                    }
                    exit(1);
                }
                if($stage) {
                    echo "***************         Step \"{$stage}\" OK\n";
                }
                if($res === true) {
                    // we're done successfully
                    echo "***************         SUCCESS!\n";
                    exit(0);
                }
                $stage = $res;
            }
        }
    }

    /**
     * Build argv string from an array
     * @param string $arguments
     * @return string
     */
    protected function buildArgString($arguments=array())
    {
    	if(!is_array($arguments) || count($arguments) == 1) {
    		return '';
    	}

    	array_shift($arguments); // drop $argv[0]

    	$argument_string = '';
    	foreach($arguments as $arg) {
    	    //If current directory or parent directory is specified, substitute with full path
    		if($arg == '.')
    		{
    			$arg = getcwd();
    		} else if ($arg == '..') {
    		    $args = dirname(getcwd());
    		}
    		$argument_string .= ' ' . escapeshellarg($arg);
    	}

    	return $argument_string;
   }

}

if(empty($argv[0]) || basename($argv[0]) != basename(__FILE__)) return;

$sapi_type = php_sapi_name();
if (substr($sapi_type, 0, 3) != 'cli') {
    die("This is command-line only script");
}
CliUpgrader::start();

