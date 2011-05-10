<?php
 if(!defined('sugarEntry'))define('sugarEntry', true);
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
include_once('config.php');
require_once('log4php/LoggerManager.php');
require_once('include/database/PearDatabase.php');
require_once('modules/Users/User.php');
require_once('include/modules.php');
require_once('include/utils.php');
// cn: set php.ini settings at entry points
setPhpIniSettings();

///////////////////////////////////////////////////////////////////////////////
////	HELPER FUNCTIONS
function json_retrieve() {
	global $beanFiles,$beanList;
	//header('Content-type: text/xml');
	require_once($beanFiles[$beanList[$_REQUEST['module']]]);
	$focus = new $beanList[$_REQUEST['module']];

	$focus->retrieve($_REQUEST['record']);

	$all_fields = array_merge($focus->column_fields,$focus->additional_column_fields);

	$js_fields_arr = array();
	print "{fields:{";

	foreach($all_fields as $field) {
		if(isset($focus->$field)) {
			$focus->$field =  from_html($focus->$field);
			$focus->$field =  preg_replace("/\r\n/","<BR>",$focus->$field);
			$focus->$field =  preg_replace("/\n/","<BR>",$focus->$field);
			array_push( $js_fields_arr , "\"$field\":\"".addslashes($focus->$field)."\"");
		}
	}
	print implode(",",$js_fields_arr);
	print "}";
	print "}";
}

function json_get_full_list() {
	global $beanFiles;
	global $beanList;

	require_once('include/utils.php');
	require_once($beanFiles[$beanList[$_REQUEST['module']]]);

	$json = getJSONobj();

	$where = str_replace('\\','', rawurldecode($_REQUEST['where']));
	$order = str_replace('\\','', rawurldecode($_REQUEST['order']));
	$focus = new $beanList[$_REQUEST['module']];
	$fullList = $focus->get_full_list($order, $where, '');
	$all_fields = array_merge($focus->column_fields,$focus->additional_column_fields);

	$js_fields_arr = array();
	
	$i=1; // js doesn't like 0 index?
	foreach($fullList as $note) {
		$js_fields_arr[$i] = array();
		
		foreach($all_fields as $field) {
			if(isset($note->$field)) {
				$note->$field = from_html($note->$field);
				$note->$field = preg_replace('/\r\n/','<BR>',$note->$field);
				$note->$field = preg_replace('/\n/','<BR>',$note->$field);
				$js_fields_arr[$i][$field] = addslashes($note->$field);
			}
		}
		$i++;
	}
	
	$out = $json->encode($js_fields_arr);
	print($out);
}
////	END HELPER FUNCTIONS
///////////////////////////////////////////////////////////////////////////////


clean_special_arguments();

// called from another file
$GLOBALS['log'] = LoggerManager::getLogger('json.php');

// check for old config format.
if(empty($sugar_config) && isset($dbconfig['db_host_name'])) {
   make_sugar_config($sugar_config);
}

insert_charset_header();

if(!empty($sugar_config['session_dir'])) {
	session_save_path($sugar_config['session_dir']);
}

session_start();

$user_unique_key = (isset($_SESSION['unique_key'])) ? $_SESSION['unique_key'] : "";
$server_unique_key = (isset($sugar_config['unique_key'])) ? $sugar_config['unique_key'] : "";

if($user_unique_key != $server_unique_key) {
	session_destroy();
	header('Location: index.php?action=Login&module=Users');
	exit();
}

if(!isset($_SESSION['authenticated_user_id'])) {
	// TODO change this to a translated string.
	session_destroy();
	die('An active session is required to export content');
}

$current_user = new User();

$result = $current_user->retrieve($_SESSION['authenticated_user_id']);
if($result == null) {
	session_destroy();
	die('An active session is required to export content');
}

$supported_functions = array('retrieve','get_full_list');
if(in_array($_REQUEST['action'],$supported_functions)) {
	call_user_func('json_'.$_REQUEST['action']);
}

sugar_cleanup();
?>
