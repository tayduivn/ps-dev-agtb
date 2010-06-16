<?php
 if(!defined('sugarEntry'))define('sugarEntry', true);
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
include_once('config.php');
require_once('log4php/LoggerManager.php');
require_once('include/database/PearDatabase.php');
require_once('modules/Users/User.php');
require_once('include/modules.php');
require_once('include/utils.php');

clean_special_arguments();
// cn: set php.ini settings at entry points
setPhpIniSettings();

if (!empty($_REQUEST['remove'])) clean_string($_REQUEST['remove'], "STANDARD");
if (!empty($_REQUEST['from'])) clean_string($_REQUEST['from'], "STANDARD");

require_once('modules/ACL/ACLController.php');
require_once('modules/Campaigns/utils.php');
$GLOBALS['log'] = LoggerManager::getLogger('removeme');
if(!empty($_REQUEST['identifier'])) {

	$keys=log_campaign_activity($_REQUEST['identifier'],'removed');
	if (!empty($keys)) {

		$id = $keys['target_id'];
		$module = trim($keys['target_type']);
		$class = $beanList[$module];
		require_once($beanFiles[$class]);
		$mod = new $class();
		$db = & PearDatabase::getInstance();

		$id = $db->quote($id);
		//no opt out for users.
		if(ereg('^[0-9A-Za-z\-]*$', $id) && $module != 'Users'){
			$query = "UPDATE $mod->table_name SET email_opt_out='on' WHERE id ='$id'";
			$status=$db->query($query);
			if($status){
				echo "*";
			}
		}
		//	record this activity in the campaing log table..
		echo "You have elected to opt out and to no longer receive emails.";
	}
}
sugar_cleanup();
?>