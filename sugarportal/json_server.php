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
 *of a third party.	Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.	You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.	See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.	Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
$global_registry_var_name = 'GLOBAL_REGISTRY';

//ignore notices
error_reporting(E_ALL ^ E_NOTICE);

$simple_log = false;
ob_start();

include_once('config.php');
require_once('log4php/LoggerManager.php');
require_once('include/database/PearDatabase.php');
require_once('modules/Users/User.php');
require_once('include/modules.php');
require_once('include/utils.php');
require_once('include/Localization/Localization.php');


clean_special_arguments();
// cn: set php.ini settings at entry points
setPhpIniSettings();

require_once('soap/SoapHelperFunctions.php');
require_once('modules/ACL/ACLController.php');
$GLOBALS['log'] = LoggerManager::getLogger('SugarCRM');
$GLOBALS['log']->debug("JSON_SERVER:");


/*
 * ADD NEW METHODS TO THIS ARRAY:
 * then create a function called "function json_$method($request_id,&$params)"
 * where $method is the method name
 */
$SUPPORTED_METHODS = array('retrieve','query','set_accept_status','get_user_array', 'get_objects_from_module', 'email', 'get_full_list');


// check for old config format.
if(empty($sugar_config) && isset($dbconfig['db_host_name'])) {
	$GLOBALS['log']->debug("JSON_SERVER:make_sugar_config:");
	make_sugar_config($sugar_config);
}

insert_charset_header();

if(!empty($sugar_config['session_dir'])) {
	session_save_path($sugar_config['session_dir']);
	$GLOBALS['log']->debug("JSON_SERVER:session_save_path:".$sugar_config['session_dir']);
}

session_start();
$GLOBALS['log']->debug("JSON_SERVER:session started");

$current_language = 'en_us'; // defaulting - will be set by user, then sys prefs

// create json parser
$json = getJSONobj();

//$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE); // modified new file

	// if the language is not set yet, then set it to the default language.
if(isset($_SESSION['authenticated_user_language']) && $_SESSION['authenticated_user_language'] != '') {
	$current_language = $_SESSION['authenticated_user_language'];
} else {
	$current_language = $sugar_config['default_language'];
}

$locale = new Localization();

$GLOBALS['log']->debug("JSON_SERVER: current_language:".$current_language);

// if this is a get, than this is spitting out static javascript as if it was a file
// wp: DO NOT USE THIS. Include the javascript inline using include/json_config.php
// using <script src=json_server.php></script> does not cache properly on some browsers
// resulting in 2 or more server hits per page load. Very bad for SSL.
if(strtolower($_SERVER['REQUEST_METHOD'])== 'get') {
	$current_user = authenticate();
	if(empty($current_user)) {
		$GLOBALS['log']->debug("JSON_SERVER: current_user isn't set");
		print "";
		exit;
	}

	$str = '';
	$str .= getAppMetaJSON();
	$GLOBALS['log']->debug("JSON_SERVER:getAppMetaJSON");

	if($_GET['module'] != '_configonly') {
		$str .= getFocusData();
		$GLOBALS['log']->debug("JSON_SERVER: getFocusData");
		$str .= getStringsJSON();
		$GLOBALS['log']->debug("JSON_SERVER:getStringsJSON");
	}

	$str .= getUserConfigJSON();
	$GLOBALS['log']->debug("JSON_SERVER:getUserConfigJSON");
	print $str;
	exit;
} else {
	// else act as a JSON-RPC server for SugarCRM
	// create result array
	$response = array();
	$response['result'] = null;
	$response['id'] = "-1";

	// authenticate user
	$current_user = authenticate();

	if(empty($current_user)) {
		$response['error'] = array("error_msg"=>"not logged in");
		print $json->encode($response);
		print "not logged in";
		exit;
	}

	// extract request
	$request = $json->decode($GLOBALS['HTTP_RAW_POST_DATA']);
	if(!is_array($request)) {
		$response['error'] = array("error_msg"=>"malformed request");
		print $json->encode($response);
		exit;
	}

	// make sure required RPC fields are set
	if(empty($request['method']) || empty($request['id'])) {
		$response['error'] = array("error_msg"=>"missing parameters");
		print $json->encode($response);
		exit;
	}

	$response['id'] = $request['id'];

	if(in_array($request['method'], $SUPPORTED_METHODS)) {
		call_user_func('json_'.$request['method'],$request['id'],$request['params']);
	} else {
		$response['error'] = array("error_msg"=>"method:".$request["method"]." not supported");
		print $json->encode($response);
		exit;
	}
}
ob_end_flush();
/// END OF SCRIPT.. the rest are the functions:



function authenticate()
{
 global $sugar_config;
 $user_unique_key = (isset($_SESSION['unique_key'])) ? $_SESSION['unique_key'] : "";
 $server_unique_key = (isset($sugar_config['unique_key'])) ? $sugar_config['unique_key'] : "";

 if ($user_unique_key != $server_unique_key) {
		$GLOBALS['log']->debug("JSON_SERVER: user_unique_key:".$user_unique_key."!=".$server_unique_key);
				session_destroy();
				return null;
 }

 if(!isset($_SESSION['authenticated_user_id']))
 {
				// TODO change this to a translated string.
		$GLOBALS['log']->debug("JSON_SERVER: authenticated_user_id NOT SET. DESTROY");
				session_destroy();
				return null;
 }

 $current_user = new User();

 $result = $current_user->retrieve($_SESSION['authenticated_user_id']);
		$GLOBALS['log']->debug("JSON_SERVER: retrieved user from SESSION");


 if($result == null)
 {
		$GLOBALS['log']->debug("JSON_SERVER: could get a user from SESSION. DESTROY");
	 session_destroy();
	 return null;
 }


 return $result;
}

/**
 * Generic retrieve for getting data from a sugarbean
 */
function json_retrieve($request_id,&$params)
{
	global $json,$current_user;
	global $beanFiles,$beanList;

	$record = $params[0]['record'];

	require_once($beanFiles[$beanList[$params[0]['module']]]);
	$focus = new $beanList[$params[0]['module']];
	$focus->retrieve($record);

	// to get a simplified version of the sugarbean
	$module_arr = populateBean($focus);

	$response = array();
	$response['id'] = $request_id;
	$response['result'] = array( "status"=>"success","record"=>$module_arr);
	$json_response = $json->encode($response);
	print $json_response;
	exit;
}

function json_get_user_array($request_id,&$params)
{
	global $json;
	$args = $params[0];

		//decode condition parameter values..
	if (is_array($args['conditions'])) {
		foreach($args['conditions'] as $key=>$condition)	{
			if (!empty($condition['value'])) {
				$args['conditions'][$key]['value']=$json->decode($condition['value']);
			}
		}
	}

	$response = array();
	$response['id'] = $request_id;
	$response['result'] = array();
	$response['result']['list'] = array();
 	$user_array = get_user_array(false, "Active", $focus->assigned_user_id, false, $args['conditions'][0]['value']);

	foreach($user_array as $id=>$name) {
		array_push($response['result']['list'], array('fields' => array('id' => $id, 'user_name' => $name), 'module' => 'Users'));
	}

	print $json->encode($response);
	exit;
}

// ONLY USED FOR MEETINGS
function meeting_retrieve($module,$record)
{
	global $json,$response;
	global $beanFiles,$beanList;
	//header('Content-type: text/xml');
	require_once($beanFiles[$beanList[$module]]);
	$focus = new $beanList[$module];

	if ( empty($module) || empty($record))
	{
		$response['error'] = array("error_msg"=>"method: retrieve: missing module or record as parameters");
		print $json->encode($response);
		exit;
	}

	$focus->retrieve($record);
$GLOBALS['log']->debug("JSON_SERVER:retrieved meeting:");
	$module_arr = populateBean($focus);

	if ( $module == 'Meetings')
	{
		$users = $focus->get_meeting_users();
	} else if ( $module == 'Calls')
	{
		$users = $focus->get_call_users();
	}

	$module_arr['users_arr'] = array();

	foreach($users as $user)
	{
		array_push($module_arr['users_arr'],	populateBean($user));
	}
	$module_arr['orig_users_arr_hash'] = array();
	foreach($users as $user)
	{
	 $module_arr['orig_users_arr_hash'][$user->id] = '1';
	}

	$module_arr['contacts_arr'] = array();

	$focus->load_relationships('contacts');
	$contacts=$focus->get_linked_beans('contacts','Contact');
	foreach($contacts as $contact)
	{
		array_push($module_arr['users_arr'], populateBean($contact));
	}

	return $module_arr;
}

// HAS MEETING SPECIFIC CODE:
function populateBean(&$focus)
{
	$all_fields = $focus->list_fields;
	// MEETING SPECIFIC
	$all_fields = array_merge($all_fields,array('required','accept_status','name')); // need name field for contacts and users
	//$all_fields = array_merge($focus->column_fields,$focus->additional_column_fields);

	$module_arr = array();

	$module_arr['module'] = $focus->object_name;

	$module_arr['fields'] = array();

	foreach($all_fields as $field)
	{
		if(isset($focus->$field))
		{
			 $focus->$field =	from_html($focus->$field);
			 $focus->$field =	preg_replace("/\r\n/","<BR>",$focus->$field);
			 $focus->$field =	preg_replace("/\n/","<BR>",$focus->$field);
			 $module_arr['fields'][$field] = $focus->$field;
		}
	}
$GLOBALS['log']->debug("JSON_SERVER:populate bean:");
	return $module_arr;
}

function construct_where(&$query_obj, $table='') {
	if (! empty($table)) {
		$table .= ".";
	}
	$cond_arr = array();

	if (! is_array($query_obj['conditions'])) {
		$query_obj['conditions'] = array();
	}

	foreach($query_obj['conditions'] as $condition) {
		 if($condition['op'] == 'contains') {
		 	array_push($cond_arr,$table.getValidDBName($condition['name'])." like '%".PearDatabase::quote($condition['value'])."%'");
		 }
		 if($condition['op'] == 'like_custom') {
		 	$like = '';
		 	if(!empty($condition['begin'])) $like .= PearDatabase::quote($condition['begin']);
		 	$like .= PearDatabase::quote($condition['value']);
		 	if(!empty($condition['end'])) $like .= PearDatabase::quote($condition['end']);
		 	array_push($cond_arr,$table.getValidDBName($condition['name'])." like '$like'");
		 } else { // starts_with
		 	array_push($cond_arr,$table.getValidDBName($condition['name'])." like '".PearDatabase::quote($condition['value'])."%'");
		 }
	}

	if($table == 'users.') {
		array_push($cond_arr,$table."status='Active'");
	}

	return implode(" {$query_obj['group']} ",$cond_arr);
}



function json_query($request_id, &$params) {
	global $json, $response, $sugar_config;
	global $beanFiles, $beanList;

	if($sugar_config['list_max_entries_per_page'] < 31)	// override query limits
		$sugar_config['list_max_entries_per_page'] = 31;

	$args = $params[0];

	//decode condition parameter values..
	if (is_array($args['conditions'])) {
		foreach($args['conditions'] as $key=>$condition)	{
			if (!empty($condition['value'])) {
				$where = $json->decode(utf8_encode($condition['value']));
				$args['conditions'][$key]['value'] = $where;
			}
		}
	}


	$list_return = array();

//_ppl($args,'','','fatal');

	if(! empty($args['module'])) {
		$args['modules'] = array($args['module']);
	}
	foreach($args['modules'] as $module) {

		require_once($beanFiles[$beanList[$module]]);
		$focus = new $beanList[$module];

		$query_orderby = '';
		if (!empty($args['order'])) {
			$query_orderby = $args['order'];
		}
		$query_limit = '';
		if (!empty($args['limit'])) {
			$query_limit = $args['limit'];
		}
		$query_where = construct_where($args, $focus->table_name);
		$list_arr = array();
		if($focus->ACLAccess('ListView', true)) {
			$curlist = $focus->get_list($query_orderby, $query_where, 0, $query_limit, -1, 0);
			$list_return = array_merge($list_return,$curlist['list']);
		}
	}

	$app_list_strings = null;
	for($i = 0;$i < count($list_return);$i++) {
		$list_arr[$i]= array();
		$list_arr[$i]['fields']= array();
		$list_arr[$i]['module']= $list_return[$i]->object_name;

		foreach($args['field_list'] as $field) {
			// handle enums
			if(	(isset($list_return[$i]->field_name_map[$field]['type']) && $list_return[$i]->field_name_map[$field]['type'] == 'enum') ||
				(isset($list_return[$i]->field_name_map[$field]['custom_type']) && $list_return[$i]->field_name_map[$field]['custom_type'] == 'enum')) {

				// get fields to match enum vals
				if(empty($app_list_strings)) {
					if(isset($_SESSION['authenticated_user_language']) && $_SESSION['authenticated_user_language'] != '') $current_language = $_SESSION['authenticated_user_language'];
					else $current_language = $sugar_config['default_language'];
					$app_list_strings = return_app_list_strings_language($current_language);
				}

				// match enum vals to text vals in language pack for return
				if(!empty($app_list_strings[$list_return[$i]->field_name_map[$field]['options']])) {
					$list_return[$i]->$field = $app_list_strings[$list_return[$i]->field_name_map[$field]['options']][$list_return[$i]->$field];
				}
			}

			$list_arr[$i]['fields'][$field] = $list_return[$i]->$field;
		}
	}


	$response['id'] = $request_id;

	$response['result'] = array( "list"=>$list_arr);
	// $json->encode if not handling ascii when mixed with utf8?
	$json_response = $json->encode($response['result']);

	$output = "{\"id\":\"$request_id\",\"result\":$json_response}";
	echo $output;
	exit;
}




function json_email($request_id,&$params)
{
	global $json,$response, $sugar_config;

	$args = $params[0];
	if($sugar_config['list_max_entries_per_page'] < 50)	// override query limits
	 $sugar_config['list_max_entries_per_page'] = 50;
	global $beanFiles,$beanList;

 $list_return = array();

 if(! empty($args['module']))
 {
	 $args['modules'] = array($args['module']);

 }
 foreach($args['modules'] as $module)
 {
	require_once($beanFiles[$beanList[$module]]);
	$focus = new $beanList[$module];

	$query_orderby = '';
	if (!empty($args['order'])) {
		$query_orderby = $args['order'];
	}
	$query_limit = '';
	if (!empty($args['limit'])) {
		$query_limit = $args['limit'];
	}
	$query_where = construct_where($args,$focus->table_name);
	$list_arr = array();

	$curlist = $focus->get_list($query_orderby, $query_where, 0, $query_limit, -1, 0);
	$list_return = array_merge($list_return,$curlist['list']);
 }

 for($i = 0;$i < count($list_return);$i++)
 {
	 $list_arr[$i]= array();
	 $list_arr[$i]['fields']= array();
	 $list_arr[$i]['module']= $list_return[$i]->object_name;

	foreach($args['field_list'] as $field)
	 {
			$list_arr[$i]['fields'][$field] = $list_return[$i]->$field;
	 }

 }
	$response['id'] = $request_id;
	$response['result'] = array( "list"=>$list_arr);
	$json_response = $json->encode($response['result']);
	print "{\"id\":\"$request_id\",\"result\":$json_response}";
	exit;

}


function json_set_accept_status($request_id,&$params)
{
 global $json,$current_user;
 global $beanFiles,$beanList;

 require_once($beanFiles[$beanList[$params[0]['module']]]);
 $focus = new $beanList[$params[0]['module']];

 $focus->id = $params[0]['record'];
 $test = $focus->set_accept_status($current_user,$params[0]['accept_status']);
 $response = array();
 $response['id'] = $request_id;

 $response['result'] = array( "status"=>"success","record"=>$params[0]['record'],'accept_status'=>$params[0]['accept_status']);

	$json_response = $json->encode($response);

	print $json_response;
	exit;

}

function json_get_objects_from_module($request_id,&$params)
{
	global	$beanList, $beanFiles, $json, $current_user;

	$module_name = $params[0]['module'];
	$offset = intval($params[0]['offset']);
	$where = $params[0]['where'];
	$max = $params[0]['max'];
	$order_by = $params[0]['order_by'];

	$class_name = $beanList[$module_name];
	require_once($beanFiles[$class_name]);
	$seed = new $class_name();
	if($where == ''){
		$where = '';
	}
	if($offset == '' || $offset == -1){
		$offset = 0;
	}
	if($max == ''){
		$max = 10;
	}

	$deleted = '0';
	$response = $seed->get_list($order_by, $where, $offset,-1,$max,$deleted);

	$list = $response['list'];
	$row_count = $response['row_count'];

	$output_list = array();
	foreach($list as $value)
	{
		$output_list[] = get_return_value($value, $module_name);
	}
	$response = array();
	$response['id'] = $request_id;
	$response['result'] = array('result_count'=>$row_count,'entry_list'=>$output_list);
		//echo $response['result'];
		$json_response = $json->encode($response);
	//echo $offset;
		print $json_response;
		exit;
}

function getUserJSON() {

}


function getUserConfigJSON()
{
 require_once('include/TimeDate2.php');
 $timedate = TimeDate2::getInstance();
 global $current_user,$global_registry_var_name,$json,$_SESSION,$sugar_config;

 if(isset($_SESSION['authenticated_user_theme']) && $_SESSION['authenticated_user_theme'] != '')
 {
	$theme = $_SESSION['authenticated_user_theme'];
 }
 else
 {
	 $theme = $sugar_config['default_theme'];
 }
 $user_arr = array();
 $user_arr['theme'] = $theme;
 $user_arr['fields'] = array();
 $user_arr['module'] = 'User';
 $user_arr['fields']['id'] = $current_user->id;
 $user_arr['fields']['user_name'] = $current_user->user_name;
 $user_arr['fields']['first_name'] = $current_user->first_name;
 $user_arr['fields']['last_name'] = $current_user->last_name;
 $user_arr['fields']['email'] = $current_user->email1;
 $userTz = $timedate->getUserTimeZone();
 $dstRange = $timedate->getDSTRange(date('Y'), $userTz);
 $user_arr['fields']['dst_start'] = $dstRange['start'];
 $user_arr['fields']['dst_end'] = $dstRange['end'];
 $user_arr['fields']['gmt_offset'] = $userTz['gmtOffset'];
 $str = "\n".$global_registry_var_name.".current_user = ".$json->encode($user_arr).";\n";
return $str;

}
function getAppMetaJSON() {
	global $json, $global_registry_var_name, $sugar_config;

	$str = "\nvar ".$global_registry_var_name." = new Object();\n";

	$sugar_config['site_url'] = preg_replace('/^http(s)?\:\/\/[^\/]+/',"http$1://".$_SERVER['HTTP_HOST'],$sugar_config['site_url']);

	if(!empty($_SERVER['SERVER_PORT']) &&$_SERVER['SERVER_PORT'] == '443') {
		$sugar_config['site_url'] = preg_replace('/^http\:/','https:',$sugar_config['site_url']);
	}
	$str .= "\n".$global_registry_var_name.".config = {\"site_url\":\"".$sugar_config['site_url']."\"};\n";

	$str .= $global_registry_var_name.".meta = new Object();\n";
	$str .= $global_registry_var_name.".meta.modules = new Object();\n";
	$modules_arr = array('Meetings','Calls');
	$meta_modules = array();

	global $beanFiles,$beanList;
	//header('Content-type: text/xml');
	foreach($modules_arr as $module) {
		require_once($beanFiles[$beanList[$module]]);
		$focus = new $beanList[$module];
		$meta_modules[$module] = array();
		$meta_modules[$module]['field_defs'] = $focus->field_defs;
	}

	$str .= $global_registry_var_name.".meta.modules.Meetings = ". $json->encode($meta_modules['Meetings'])."\n";
	$str .= $global_registry_var_name.".meta.modules.Calls = ". $json->encode($meta_modules['Calls'])."\n";
	return $str;
}


function getFocusData()
{
 global $json,$global_registry_var_name;

 if ( empty($_REQUEST['module']) )
 {
	 return '';
 }
 else if ( empty($_REQUEST['record'] ) )
 {
	// return '';
	 return "\n".$global_registry_var_name.'["focus"] = {"module":"'.$_REQUEST['module'].'",users_arr:[],fields:{"id":"-1"}}'."\n";
 }

 $module_arr = meeting_retrieve($_REQUEST['module'], $_REQUEST['record']);
 return "\n".$global_registry_var_name."['focus'] = ". $json->encode($module_arr).";\n";
}

function getStringsJSON()
{

	//set module and application string arrays based upon selected language
 // $app_strings = return_application_language($current_language);
	global $current_language;
	$currentModule = 'Calendar';
	$mod_list_strings = return_mod_list_strings_language($current_language,$currentModule);

 global $json,$global_registry_var_name;
	 $str = "\n".$global_registry_var_name."['calendar_strings'] =	{\"dom_cal_month_long\":". $json->encode($mod_list_strings['dom_cal_month_long']).",\"dom_cal_weekdays_long\":". $json->encode($mod_list_strings['dom_cal_weekdays_long'])."}\n";
	if ( empty($_REQUEST['module']))
	{
	 $_REQUEST['module'] = 'Home';
	}
	$currentModule = $_REQUEST['module'];
	$mod_strings = return_module_language($current_language,$currentModule);
	 return	$str . "\n".$global_registry_var_name."['meeting_strings'] =	". $json->encode($mod_strings)."\n";

}

function json_get_full_list($request_id, &$params) {
	global $json; // pre-instantiated above

	global $beanFiles;
	global $beanList;
	require_once($beanFiles[$beanList[$params[0]['module']]]);

	$where = str_replace('\\','', rawurldecode($params[0]['where']));
	$order = str_replace('\\','', rawurldecode($params[0]['order']));
	$focus = new $beanList[$params[0]['module']];

	$fullList = $focus->get_full_list($order, $where, '');
	$all_fields = array_merge($focus->column_fields,$focus->additional_column_fields);

	$js_fields_arr = array();

	if(isset($fullList) && !empty($fullList)) { // json error if this isn't defensive
		$i=0;
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
	}

	$fin['id'] = $request_id;
	$fin['result'] = $js_fields_arr;
	$out = $json->encode($fin);

	print($out);
}

sugar_cleanup();
exit();

?>