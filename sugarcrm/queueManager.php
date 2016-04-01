<?php
if (!defined('sugarEntry')) {
    define('sugarEntry', true);
}
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

use Sugarcrm\Sugarcrm\JobQueue\Helper\ProcessControl;
use Sugarcrm\Sugarcrm\JobQueue\Manager\Manager;
use Sugarcrm\Sugarcrm\Logger\LoggerTransition;

chdir(dirname(__FILE__));
define('ENTRY_POINT_TYPE', 'api');
require_once 'include/entryPoint.php';

$sapi_type = php_sapi_name();
if (substr($sapi_type, 0, 3) != 'cli') {
    sugar_die('queueManager.php is CLI only.');
}

$helper = new ProcessControl('queueManager');
if ($helper->isServiceLocked()) {
    sugar_die('JobQueue fails to start. The service is locked.');
}
$helper->modifyServiceProcessName();

SugarMetric_Manager::getInstance()->setMetricClass('background')->setTransactionName('cron');
if (empty($current_language)) {
    $current_language = $sugar_config['default_language'];
}
$app_list_strings = return_app_list_strings_language($current_language);
$app_strings = return_application_language($current_language);

$current_user = BeanFactory::getBean('Users');
$current_user->getSystemUser();

TimeDate::getInstance()->allow_cache = false;

$manager = new Manager(new LoggerTransition(\LoggerManager::getLogger()));
register_shutdown_function(array($manager, 'shutdownHandler'));
$manager->run();
