<?php
 if(!defined('sugarEntry'))define('sugarEntry', true);
 /*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
 //DCE ONLY
/**
 * This file is to be placed on a cluster and will communicate with the DCE database
 * to pull down cron jobs to run on particular DCE instances.
 */
//1 ask DCE table for any jobs it can run
//2 call cron.php on those instances
//3 wait and repeat. crontab will run this file.
require_once('cron_library.php');
$serverName = '';
if(!empty($_SERVER['HOSTNAME'])){
	$serverName = $_SERVER['HOSTNAME'];
}elseif(!empty($_SERVER['COMPUTERNAME'])){
	$serverName = $_SERVER['COMPUTERNAME'];
}

$jobsToRun = lockJobs($serverName);
$is_windows = is_windows_os();

foreach($jobsToRun as $job){
	//run cron.php for that job, we should run cron.php in the background.
	
	$call = 'php -f cron_wrapper.php instance_id='.$job['instance_id'] .' instance_path='.base64_encode($job['instance_path']);
	$cmd = '';
	if($is_windows){
		$cmd = 'start /b '.$call;
	}else{
		$cmd = $call.' >> /dev/null &';
	}
	exec($cmd);
}

?>