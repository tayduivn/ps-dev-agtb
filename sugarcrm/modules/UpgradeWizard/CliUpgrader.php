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
        self::bannerError($msg);
        self::usage();
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

    protected static function verifyArguments($context, $argv)
    {
        if(empty($context['source_dir']) || !is_dir($context['source_dir'])) {
            self::argError("3rd parameter must be a valid directory: {$argv[3]}.");
        }

        if(!is_file("{$context['source_dir']}/include/entryPoint.php") || !is_file("{$context['source_dir']}/config.php")) {
            self::argError("{$context['source_dir']} is not a SugarCRM directory.");
        }

        if(!is_file($context['zip'])) { // valid zip?
            self::argError("First argument must be a full path to the patch file: {$argv[1]}.");
        }
        return true;
    }

    public static function parseArgs($argv)
    {
        if(count($argv) < 5) {
            $cnt = count($argv);
            self::argError("Upgrader requires 4 argumens, $cnt given");
        }
        if(defined('PHP_BINDIR')) {
        	$php_path = PHP_BINDIR."/";
        } else {
        	$php_path = '';
        }
        if(!file_exists($php_path . 'php')) {
            $php_path = '';
        }
        $context = array(
            'zip' => realpath($argv[1]),
            'log' => $argv[2],
            'source_dir' => realpath($argv[3]),
            'admin' => $argv[4],
            'php' => $php_path."php",
            'script' => __FILE__,
            'argv' => $argv,
        );
        return $context;
    }

    /**
     * Execution starts here
     */
    public static function start()
    {
        global $argv;
        $context = self::parseArgs($argv);
        self::verifyArguments($context, $argv);
        if(isset($argv[5])) {
            $stage = $argv[5];
        } else {
            $stage = null;
        }
        chdir($context['source_dir']);
        $upgrader = new self($context);
        if($stage && $stage != 'continue') {
            // Run one step
            if($upgrader->run($stage)) {
                exit(0);
            } else {
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

$sapi_type = php_sapi_name();
if (substr($sapi_type, 0, 3) != 'cli') {
    die("This is command-line only script");
}
CliUpgrader::start();

