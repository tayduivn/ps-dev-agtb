<?php
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
 
if ($argc == 3 && strpos($argv[1], 'instance_id=') !== false && strpos($argv[2], 'instance_path=') !== false){
	$instance_path_args = explode("=", $argv[2]);
	$instance_path = base64_decode($instance_path_args[1]);
	if(!empty($instance_path)){
		//call cron
		$current_dir = getcwd();
		chdir($instance_path);
		$GLOBALS['DCE_CALL'] = true;
		include('cron.php');
		//once cron completes, update the database
		chdir($current_dir);
	}
	require_once('cron_library.php');
	$instance_args = explode("=", $argv[1]);
	echo 'Unlocking instance: '.$instance_args[1];
	unlockJob($instance_args[1]);
}

?>