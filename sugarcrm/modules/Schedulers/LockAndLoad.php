<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
//FILE SUGARCRM flav=int ONLY
if(empty($GLOBALS['log'])) {
	
	$GLOBALS['log'] = LoggerManager::getLogger('SugarCRM'); 	
}
$GLOBALS['log']->debug('LockAndLoad.php accessed');
require_once('modules/Schedulers/SchedulerMonitor.php');
ignore_user_abort(true);// keep processing if browser is closed
set_time_limit(0);// no time out

$monitor = new SchedulerMonitor();
$monitor->listen();

?>
