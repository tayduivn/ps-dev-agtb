<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * Configure Default Dial code page
 *
 * Author: Felix Nilam
 * Date: 17/08/2007
 ********************************************************************************/

require_once ('include/Sugar_Smarty.php');
require_once('include/utils.php');
require_once('modules/Configurator/Configurator.php');

global $app_strings;
global $timedate;
global $app_list_strings;
global $mod_strings;
global $sugar_version, $sugar_config;
global $theme;
global $current_user;

$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";
require_once($theme_path.'layout_utils.php');

$GLOBALS['log']->info("Dial config setting");

if(!is_admin($current_user)){
	sugar_die("Unauthorised Access to Administration");
}

$focus = new Administration();

// Handle Save
if(isset($_POST['save']) || $_REQUEST['ajax'] == 1){
	$country_code= $_REQUEST['country_code'];
	$area_code = $_REQUEST['area_code'];
	$itl_code = $_REQUEST['itl_code'];
	$strip_out = $_REQUEST['strip_out'];
	$dial_out_no = $_REQUEST['dial_out_no'];
	$prepend = $_REQUEST['prepend'];
	if(!isset($prepend)){
		$prepend = "false";
	}
	if(!isset($strip_out)){
		$strip_out = "false";
	}
	// setup config string
	$config_string = "<?php\n";
	$config_string .= "\$default_dial_code = array(\n";
	$config_string .= "'prepend_dial_out_no' => ".$prepend.",\n";
	$config_string .= "'dial_out_no' => '".$dial_out_no."',\n";
	$config_string .= "'strip_intl_area_code' => ".$strip_out.",\n";
	$config_string .= "'international_code' => '".$itl_code."',\n";
	$config_string .= "'country_code' => '".$country_code."',\n";
	$config_string .= "'area_code' => '".$area_code."'\n);\n?>";
	
	$fp = fopen('fonality/include/normalizePhone/default_dial_code.php','w');
	fwrite($fp, $config_string);
	fclose($fp);
	
	// Save configuration
	if($_REQUEST['ajax']){
		$_POST['system_create_call_on_dial'] = $_REQUEST['system_create_call_on_dial'];
		$_POST['system_tapidial_on'] = $_REQUEST['system_tapidial_on'];
	}
	$focus->saveConfig();
	
	// redirect to Admin index
	if($_REQUEST['ajax']){
		return 1;
	} else {
		header("Location: index.php?module=Administration&action=index");
		exit();
	}
}

$focus->retrieveSettings();
require_once('fonality/include/normalizePhone/default_dial_code.php');
$tpl = new Sugar_Smarty();

// include extra mod strings
$mod_strings['LBL_TAPIDIAL'] = 'UAE PBX Dialer';
$mod_strings['LBL_TAPIDIAL_ON'] = 'Enable click to dial?';
$mod_strings['LBL_TAPIDIAL_ON_DESC'] = 'Allow users to click on phone numbers to call using the Fonality phone system.';
$mod_strings['LBL_DIAL_CREATE_CALL'] = 'Create Call record?';
$mod_strings['LBL_DIAL_CREATE_CALL_DESC'] = 'After user clicks to dial, the system will redirect the user to Call edit screen to create a new call record.';
$mod_strings['LBL_DIAL_CALL_ASSISTANT'] = 'Open UAE Call Assistant when dialing?';
$mod_strings['LBL_DIAL_CALL_ASSISTANT_DESC'] = 'Redirect users to the UAE Call Assistant screen when phone numbers are dialed(clicked). This will override the previous option (Create Call when dialing)';
$mod_strings['LBL_SKYPEOUT_ON_DESC'] = 'Allows users to click on phone numbers to call using SkypeOut&reg;.';
$mod_strings['LBL_SKYPEOUT_ON'] = 'Enable SkypeOut&reg; integration?';
$mod_strings['LBL_SKYPEOUT_TITLE'] = 'SkypeOut&reg;';

$tpl->assign("MOD", $mod_strings);
$tpl->assign("APP", $app_strings);
if(!empty($focus->settings['system_skypeout_on'])){
	$tpl->assign("system_skypeout_on_checked", "CHECKED");
}
if(!empty($focus->settings['system_tapidial_on'])){
	$tpl->assign("system_tapidial_on_checked", "CHECKED");
}
if(!empty($focus->settings['system_create_call_on_dial'])){
	$tpl->assign("system_create_call_on_dial_checked", "CHECKED");
}
if(!empty($focus->settings['system_call_assistant_on_dial'])){
	$tpl->assign("system_call_assistant_on_dial_checked", "CHECKED");
}

$tpl->assign("USER_DATEFORMAT", '('.$timedate->get_user_date_format().')');
$tpl->assign("CALENDAR_DATEFORMAT", $timedate->get_cal_date_format());
$time_format = $timedate->get_user_time_format();
$tpl->assign("TIME_FORMAT", '('.$time_format.')');

// assign config 
$tpl->assign("ITL_CODE", $default_dial_code['international_code']);
$tpl->assign("COUNTRY_CODE", $default_dial_code['country_code']);
$tpl->assign("AREA_CODE", $default_dial_code['area_code']);
$tpl->assign("DIAL_OUT_NO", $default_dial_code['dial_out_no']);
if($default_dial_code['strip_intl_area_code']){
	$tpl->assign("STRIP_OUT", "true");
}
if($default_dial_code['prepend_dial_out_no']){
	$tpl->assign("PREPEND", "true");
}
$tpl->display('modules/Administration/ConfigureDialSettings.tpl');

require_once('include/javascript/javascript.php');
$javascript = new javascript();
$javascript->setFormName('EditView');
$javascript->addFieldGeneric('country_code','int',$mod_strings['LBL_DEFAULT_COUNTRY_CODE'], 'true');
$javascript->addFieldGeneric('itl_code','int',$mod_strings['LBL_DEFAULT_ITL_CODE'], 'true');
echo $javascript->getScript();
?>
