<?php

define('sugarEntry', true);
define('ENTRY_POINT_TYPE', 'api');

require_once 'include/entryPoint.php';

$logger = LoggerManager::getLogger();

$sapi_type = php_sapi_name();
if (substr($sapi_type, 0, 3) != 'cli') {
    $logger->fatal('Invalid Call to CLI script');
    exit(1);
}

$logger->debug(sprintf('%s: Starting upgrade script', $_SERVER['argv'][0]));

try {
    $cf = new Configurator();
    $cf->loadConfig();
    $cf->config['languages'] = array('en_us' => 'English (US)');


    $cf->handleOverride();
} catch (Exception $e) {
    $logger->fatal(
            sprintf(
                    '%s: Upgrade failed: calling UpdateConfigFile failed with error: %s', $_SERVER['argv'][0], $e->getMessage()
            )
    );
    exit(1);
}

$logger->debug(sprintf('%s: finished UpdateConfigFile call', $_SERVER['argv'][0]));

exit(0);
