<?php
require_once 'modules/UpgradeWizard/UpgradeDriver.php';

/**
 * Command-line upgrader
 */
class CliUpgrader extends Upgrader
{
    /*
     * CLI arguments: Zipfile Logfile Sugardir Adminuser [Stage]
     */
    public function runStage($stage)
    {
        $argv[] = $stage;
        $cmd = "{$this->context['php']} -f {$this->context['script']} " . $this->buildArgString($this->context['argv']);
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
Usage: php -f silentUpgrade.php [upgradeZipFile] [logFile] [pathToSugarInstance] [admin-user]

Example:
    [path-to-PHP/]php -f silentUpgrade.php [path-to-upgrade-package/]SugarEnt-Upgrade-6.6.x-to-6.7.0.zip [path-to-log-file/]silentupgrade.log  [path-to-sugar-instance/] admin

Arguments:
    upgradeZipFile                       : Upgrade package file.
    logFile                              : Silent Upgarde log file.
    pathToSugarInstance                  : Sugar instance being upgraded.
    admin-user                           : admin user performing the upgrade
    logFile                              : Upgrade log

eoq2;
        echo $usage;
    }

    protected static function verifyArguments($argv)
    {
        if(count($argv) < 5) {
            $cnt = count($argv);
            self::argError("Upgrader requires 4 argumens, $cnt given");
        }
        if(empty($argv[3])|| !is_dir($argv[3])) {
            self::argError("3rd parameter must be a valid directory.  Tried to cd to [ {$argv[3]} ].");
        } else {
            chdir($argv[3]);
        }

        if(!is_file("{$argv[3]}/include/entryPoint.php") || !is_file("{$argv[3]}/config.php")) {
            self::argError("$argv[3] is not a SugarCRM directory.");
        }

        if(!is_file($argv[1])) { // valid zip?
            self::argError("First argument must be a full path to the patch file. Got [ {$argv[1]} ].");
        }
        return true;
    }

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
        $context = array(
            'zip' => $argv[1],
            'log' => $argv[2],
            'source_dir' => $argv[3],
            'admin' => $argv[4],
            'php' => $php_path."php",
            'script' => $argv[0]
        );
        return $context;
    }

    /**
     * Execution starts here
     */
    public static function start()
    {
        global $argv;
        self::verifyArguments($argv);
        if(isset($argv[5])) {
            $stage = $argv[5];
        } else {
            $stage = null;
        }
        $upgrader = new self(self::parseArgs($argv));
        if($stage) {
            // Run one step
            if($upgrader->run($stage)) {
                exit(0);
            } else {
                exit(1);
            }
        } else {
            // whole loop
            while(1) {
                $res = $upgrader->runStep($stage);
                if($res === false) {
                    echo "***************         Step \"{$stage}\" FAILED!\n";
                    exit(1);
                }
                echo "***************         Step \"{$stage}\" OK\n";
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
    	if(!is_array($arguments) || count($argumens) == 1) {
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

