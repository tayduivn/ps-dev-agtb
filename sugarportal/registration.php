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
require_once ('config.php'); // provides $sugar_config
// load up the config_override.php file.  This is used to provide default user settings
if(is_file('config_override.php')) {
    require_once ('config_override.php');
}
require_once ('include/utils.php');
clean_special_arguments();
clean_incoming_data();

require_once ('log4php/LoggerManager.php');
$GLOBALS['log'] = LoggerManager :: getLogger('SugarCRM');

$theme = 'Sugar';
$image_path = 'themes/'.$theme.'/images/';

require_once('themes/' . $theme . '/layout_utils.php');
if(isset($_SESSION['authenticated_user_language']) && $_SESSION['authenticated_user_language'] != '') {
    $current_language = $_SESSION['authenticated_user_language'];
} else {
    $current_language = $sugar_config['default_language'];
}
$app_strings = return_application_language($current_language);
$mod_strings = return_module_language($current_language , 'Leads');

require_once('include/Portal/Portal.php');
$portal = new Portal();


global $sugar_config;
$result = $portal->leadLogin($sugar_config['portal_username'], $sugar_config['portal_password']);
$portal->handleResult($result);
require_once('include/TimeDate.php');
require_once('sugar_version.php');
$timedate = new TimeDate();
echo '<style type="text/css">@import url("themes/Sugar/style.css?s=&c="); </style>';
echo '<script src="include/javascript/yui/YAHOO.js"></script>';
echo '<script src="include/javascript/yui/dom.js"></script>';
echo '<link rel="stylesheet" type="text/css" media="all" href="themes/Sugar/calendar-win2k-cold-1.css?s=' . $sugar_version . '&c=' . $sugar_config['js_custom_version'] . '">';
echo '<script type="text/javascript" src="include/javascript/sugar_3.js"></script>';
echo '<script>jscal_today = ' . (1000 * strtotime($timedate->handle_offset(gmdate('Y-m-d H:i:s', gmmktime()), 'Y-m-d H:i:s'))) . '; if(typeof app_strings == "undefined") app_strings = new Array();</script>';
echo '<script type="text/javascript" src="jscalendar/calendar.js?s=' . $sugar_version . '&c=' . $sugar_config['js_custom_version'] . '"></script>';
echo '<script type="text/javascript" src="jscalendar/lang/calendar-en.js?s=' . $sugar_version . '&c=' . $sugar_config['js_custom_version'] . '"></script>';
echo '<script type="text/javascript" src="jscalendar/calendar-setup_3.js?s=' . $sugar_version . '&c=' . $sugar_config['js_custom_version'] . '"></script>';
if(empty($_REQUEST['action'])) {
    include('modules/Leads/EditView.php');
}
else {
    include('modules/Leads/Save.php');
    echo $mod_strings['LBL_SAVED'];
    echo '<br><br><a href="index.php">' . $mod_strings['LBL_CLICK_TO_RETURN'] . '</a>';

}



?>
