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
require_once('modules/Campaigns/utils.php');

///////////////////////////////////////////////////////////////////////////////
////	PREP FOR SCHEDULER PID
//BEGIN SUGARCRM flav=int ONLY
$GLOBALS['log']->debug('launching job:');
//END SUGARCRM flav=int ONLY

$cachePath = sugar_cached('modules/Schedulers');
$pid = 'pid';
if(!is_dir($cachePath)) {
	mkdir_recursive($cachePath);
}
if(!is_file($cachePath.'/'.$pid)) {
	write_array_to_file('timestamp', array(strtotime(date('H:i'))) , $cachePath.'/'.$pid);
	require_once($cachePath.'/'.$pid);
} else {
	require_once($cachePath.'/'.$pid);
}
////	END PREP FOR SCHEDULER PID
///////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////////////////////////////////////
////	EXECUTE IF VALID TIME (NOT DDOS)
//if($timestamp[0] < strtotime(date('H:i'))) {
if(true) {
	write_array_to_file('timestamp', array(strtotime(date('H:i'))) , $cachePath.'/'.$pid);
	if(!unlink($cachePath.'/'.$pid)) {  // remove cache file
	}

	if(empty($GLOBALS['log'])) { // setup logging
		require_once('include/SugarLogger/LoggerManager.php');
		$GLOBALS['log'] = LoggerManager::getLogger('SugarCRM');
	}

	if((!empty($_REQUEST['type'])) && ($_REQUEST['type'] == 'job')) { // spin off a "job" thread
		if(!empty($_REQUEST['job_id'])) {
			require_once('modules/SchedulersJobs/SchedulersJob.php');

			// if run from cron, we need to run jobs as Admin
			if(empty($current_user)) {
				require_once('modules/Users/User.php');
				$current_user = BeanFactory::getBean('Users', '1');
			}

			$job_id = $_REQUEST['job_id'];

			ob_implicit_flush();
			ignore_user_abort(true); // keep processing if browser is closed
			set_time_limit(0); // no timeout to allow long jobs (mass-mailings) to go through

			$job = BeanFactory::getBean('SchedulersJobs', $_REQUEST['record']);
			$job->runtime = TimeDate::getInstance()->nowDb();

			if($job->startJob($job_id)) {
				$GLOBALS['log']->debug('----->Job [ '.$job_id.' ] was fired successfully');
				//BEGIN SUGARCRM flav=int ONLY
//				_pp('----->Job [ '.$job_id.' ] was fired successfully');
				//END SUGARCRM flav=int ONLY
				return;
			} else {
				$GLOBALS['log']->fatal('JOB FAILURE job [ '.$job_id.' ] could not complete successfully.');
				//BEGIN SUGARCRM flav=int ONLY
//				_pp('----->Job FAILURE job [ '.$job_id.' ] could not complete successfully.');
				//END SUGARCRM flav=int ONLY
				return;
			}
		} else {
			$GLOBALS['log']->fatal('JOB FAILURE schedulers.php called with no job_id.  Suiciding this thread.');
			//BEGIN SUGARCRM flav=int ONLY
//			_pp('JOB FAILURE JobThread.php called with no job_id.  Suiciding this thread.');
			//END SUGARCRM flav=int ONLY
			die();
		}



	}
//BEGIN SUGARCRM flav=int ONLY
//	elseif((!empty($_REQUEST['type'])) && ($_REQUEST['type'] == 'sncb')) { // launch the daemon
//		$GLOBALS['log']->debug('----->Monitor called START on Daemon in schedulers.php');
//		include('modules/Schedulers/SDLauncher.php');
//		return;
//	} else {	// launch the monitor
//		$GLOBALS['log']->debug('----->Monitor will launch from schedulers.php');
//		include('modules/Schedulers/LockAndLoad.php');
//		return;
//	}
//END SUGARCRM flav=int ONLY
}
//BEGIN SUGARCRM flav=int ONLY
else {
	$GLOBALS['log']->debug('did not do anything');
}
//END SUGARCRM flav=int ONLY


?>
