<?php
if(empty($argv) || empty($argc) || $argc != 3) {
    die("Use UpgradeModule.php /path/to/sugar module");
}

$sapi_type = php_sapi_name();
if (substr($sapi_type, 0, 3) != 'cli') {
    die("UpgradeModule.php is CLI only.");
}

chdir($argv[1]);
define('ENTRY_POINT_TYPE', 'api');
if(!defined('sugarEntry'))define('sugarEntry', true);
require_once('include/entryPoint.php');

function scriptErrorHandler($errno, $errstr, $errfile, $errline, $errcontext)
{
    $GLOBALS['log']->fatal("PHP: [$errno] $errstr in $errfile at $errline"."\n".var_export(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS), true));
}

require_once 'modules/UpgradeWizard/SidecarUpdate/SidecarMetaDataUpgrader.php';
class SidecarMetaDataUpgrader2 extends SidecarMetaDataUpgrader
{
    public function logUpgradeStatus($msg)
    {
        $GLOBALS['log']->info($msg);
    }
}

$GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
$smdUpgrader = new SidecarMetaDataUpgrader2();
$smdUpgrader->setModule($argv[2]);
set_error_handler('scriptErrorHandler', E_ALL & ~E_STRICT & ~E_DEPRECATED);
$smdUpgrader->upgrade();
$fail = $smdUpgrader->getFailures();
if(!empty($fail)) {
    echo "***FAILURE***\n".join("\n", $fail)."\n";
    exit(1);
} else {
    echo "SUCCESS!\nPlease check the result, and if you are satisfied, please delete these files:\n".join("\n", $smdUpgrader->getFilesForRemoval());
    echo "\n";
    exit(0);
}

