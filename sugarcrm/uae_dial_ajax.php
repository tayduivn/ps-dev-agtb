<?php
/***********************************************
 * Pass a PBX url through curl and return the redirect url
 *
 * Author: Felix Nilam
 * Date: 01/12/2007
 **********************************************/
if(!defined('sugarEntry')) define('sugarEntry', true);
require_once('include/entryPoint.php');
require_once('modules/Users/User.php');
require_once('modules/Administration/Administration.php');
require_once('UAE/common/utils.php');
require_once('modules/fonuae_PBXSettings/fonuae_PBXSettings.php');

session_start();

// add session checking
if(empty($_SESSION['authenticated_user_id'])){
	die("error:Your SugarCRM session has expired");
}

$current_user = new User();
$current_user->retrieve($_SESSION['authenticated_user_id']);

$system_config = new Administration();
$system_config->retrieveSettings('system');
$app_list_strings = return_app_list_strings_language($sugar_config['default_language']);

$parent_type = $_REQUEST['parent_type'];
$parent_id = $_REQUEST['parent_id'];
$contact_id = $_REQUEST['contact_id'];
$action = $_REQUEST['action'];
$phone = $_REQUEST['phone'];

$uri = dialURL($phone, $parent_type, $parent_id, $contact_id, $action);
logUAE('click2call', "Dial URL: $uri");

if($uri == "PBX Settings not found"){
	echo "error:Authentication failed";
	exit;	
}

logFONactivity('click2call');

//split it with & characters to get the success_redir
$split = split("&", $uri);
$test = $split;
foreach($split as $key => $arg){
	if(preg_match('/^success_redir=/', $arg)){
		$redir = urldecode(substr($arg, 14));
	}
	if(preg_match('/_redir=/', $arg)){
		unset($test[$key]);
	}	
}

$pbx_url = implode("&",$test);
$vars = array();
// build the pbx url arg
foreach($test as $arg){
	$name = split("=", $arg);
	$vars[$name[0]] = $name[1];
}

logUAE('click2call', "call.cgi: $pbx_url");

// call the pbx url through curl
$ch = curl_init($pbx_url);
curl_setopt($ch, CURLOPT_HEADER, 1);
//curl_setopt($ch, CURLOPT_POST, 1);
//curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$ret = curl_exec($ch);
curl_close($ch);
if(preg_match('/400/', $ret) || empty($ret)){
	preg_match('/[0-9]+=(.*)/', $ret, $matches);
	logUAE('click2call', "Error: ". $matches[1]);
	logUAE('click2call', "CURL response: $ret");
	if(!empty($matches[1])){
		echo "error:" . $matches[1];
	} else {
		echo "error:Unknown Error";
	}
} else {
	logUAE('click2call', "Success. Redir: $redir");
	echo $redir;
}

function dialURL($phone, $parent_type, $parent_id, $contact_id, $action){
	global $current_user;
	global $system_config;
	global $app_list_strings;
	global $sugar_config;
	require('fonality/include/normalizePhone/default_dial_code.php');
	require_once('fonality/include/normalizePhone/normalizePhone.php');

	$strip_intl_area_code = $default_dial_code['strip_intl_area_code'];
	$prepend_dial_out_no = $default_dial_code['prepend_dial_out_no'];
	$dial_out_no = $default_dial_code['dial_out_no'];
	$international_code = $default_dial_code['international_code'];

	if(empty($phone)){
		return '';
	} else {
		$nphone = normalizePhone($phone, $current_user);

		// strip out international and area code accordingly
		if($strip_intl_area_code){
			$nphone = strip_intl_area_code($nphone, $current_user);
		}

		// Prepend the number with dial out number
		/*if($prepend_dial_out_no){
			$nphone = $dial_out_no.$nphone;
		} else {*/
			// handle internal extension
			if(substr($phone, 0, 1) == "x"){
				$nphone = substr($nphone, 1);
			}
		//}

		// replace any "+" with international code
		$nphone = str_replace("+",$international_code, $nphone);

		$pbx_url = "https://cp.fonality.com";
		
		// get the pbx_settings
		$pbx_setting = new fonuae_PBXSettings();
		$pbx_setting->retrieve_by_string_fields(array('assigned_user_id' => $current_user->id));
		if(empty($pbx_setting->id)){
			// just return error if no PBX settings found
			return "PBX Settings not found";
		}
		
		$call_url = $pbx_url."/call.cgi?FONcall=1&number=".$nphone."&username=".urlencode($pbx_setting->username)."&auth_hash=".$pbx_setting->password."&calleridname=Fonality%20UAE&autooffhook=1";

		if($system_config->settings['system_call_assistant_on_dial'] == '1'){
			$call_url .= "&success_redir=". urlencode($sugar_config['site_url']."/UAECallAssistant.php?action=UAECallAssistant&opt=1&direction=Outbound&phone=".$phone);
		} else if($system_config->settings['system_create_call_on_dial'] == '1'){
			$call_url .= '&success_redir='. urlencode($sugar_config['site_url']."/uae_create_call_on_dial.php?phone=".urlencode($phone)."&parent_type=".$parent_type."&parent_id=".$parent_id."&contact_id=".$contact_id);
		} else {
			$_SESSION['call_phone_number'] = normalizePhone($phone);
			$_SESSION['call_parent_type'] = $parent_type;
			$_SESSION['call_parent_id'] = $parent_id;
			$_SESSION['call_contact_id'] = $contact_id;

			$referer = $sugar_config['site_url']."/index.php?module=".$parent_type."&action=".$action;
			if($action != 'index') $referer .= "&record=".$parent_id;
		}

		// Add unsucessful redirect page
		$call_url .= "&failed_redir=".urlencode($sugar_config['site_url']."/index.php?module=Calls&action=DialFailed");

		return $call_url;
	}
}
?>
