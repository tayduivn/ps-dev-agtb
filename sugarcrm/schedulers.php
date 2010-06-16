<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
//FILE SUGARCRM flav=int ONLY
require_once('modules/Campaigns/utils.php');

///////////////////////////////////////////////////////////////////////////////
////	PREP FOR SCHEDULER PID
//BEGIN SUGARCRM flav=int ONLY
$GLOBALS['log']->debug('launching job:');
//END SUGARCRM flav=int ONLY

$cachePath = $GLOBALS['sugar_config']['cache_dir'].'modules/Schedulers';
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
				$current_user = new User();
				$current_user->retrieve('1');
			}

			$job_id = $_REQUEST['job_id'];

			ob_implicit_flush();
			ignore_user_abort(true); // keep processing if browser is closed
			set_time_limit(0); // no timeout to allow long jobs (mass-mailings) to go through

			$job = new SchedulersJob();
			$job->retrieve($_REQUEST['record']);
			$job->runtime = gmdate('Y-m-d H:i:s', strtotime('now'));

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
