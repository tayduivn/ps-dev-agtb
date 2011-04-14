<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
/*********************************************************************************
 * $Id: SetTimezone.php 53551 2010-01-08 13:49:00Z jmertic $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/



require_once('include/JSON.php');
require_once('include/timezone/timezones.php');

require_once('modules/Users/Forms.php');



global $app_strings;
global $app_list_strings;
global $mod_strings;

$admin = new Administration();
$admin->retrieveSettings("notify");

//BEGIN SUGARCRM flav=pro || flav=sales ONLY
if ( isset($_SESSION['isMobile']) ) {
	session_destroy();
	session_start();
    $_SESSION['login_error'] = $mod_strings['ERR_NO_LOGIN_MOBILE'];	
    header("Location: index.php?module=Users&action=Login&mobile=1");
    sugar_cleanup(true);
}
//END SUGARCRM flav=pro || flav=sales ONLY

///////////////////////////////////////////////////////////////////////////////
////	HELPER FUNCTIONS
function lookupTimezone_special($userOffset){
	$defaultZones = array('America/New_York'=>1, 'America/Los_Angeles'=>1,'America/Chicago'=>1, 'America/Denver'=>1,'America/Anchorage'=>1, 'America/Phoenix'=>1, 'Europe/Amsterdam'=>1,'Europe/Athens'=>1,'Europe/London'=>1, 'Australia/Sydney'=>1, 'Australia/Perth'=>1, 'Asia/Tokyo'=>1);
	global $timezones;

	$gmtOffset = $userOffset;
	$selectedZone = ' ';
	foreach($timezones as $zoneName=>$zone){
		if($zone['gmtOffset'] == $gmtOffset){
			$selectedZone = $zoneName;
		}
		if(!empty($defaultZones[$selectedZone]) ){
			return $selectedZone;
		}
	}
	return $selectedZone;
}
////	END HELPER FUNCTIONS
///////////////////////////////////////////////////////////////////////////////

if(isset($_REQUEST['userOffset'])) { // ajax call to lookup timezone
    echo 'userTimezone = "' . lookupTimezone_special($_REQUEST['userOffset']) . '";';
    die();
}
$admin = new Administration();
$admin->retrieveSettings();
$sugar_smarty = new Sugar_Smarty();
$sugar_smarty->assign('MOD', $mod_strings);
$sugar_smarty->assign('APP', $app_strings);


$selectedZone = $current_user->getPreference('timezone');
if(empty($selectedZone) && !empty($_REQUEST['gmto'])) {
	$selectedZone = lookupTimezone_special(-1 * $_REQUEST['gmto']);
}

$timezoneOptions = '';
ksort($timezones);

foreach($timezones as $key => $value) {
	if( $selectedZone== $key) {
		$selected = " SELECTED";
	} else { 
		$selected = "";
	}
	if(!empty($value['dstOffset'])) {
		$dst = " (+DST)";
	} else {
		$dst = "";
	}
	$gmtOffset = ($value['gmtOffset'] / 60);
	if(!strstr($gmtOffset,'-')) {
		$gmtOffset = "+".$gmtOffset;
	}
	$timezoneOptions .= "<option value='$key'".$selected.">".str_replace(array('_','North'), array(' ', 'N.'),$key). " (GMT".$gmtOffset.") ".$dst."</option>";
}
$sugar_smarty->assign('TIMEZONEOPTIONS', $timezoneOptions);
$sugar_smarty->display('modules/Users/SetTimezone.tpl');