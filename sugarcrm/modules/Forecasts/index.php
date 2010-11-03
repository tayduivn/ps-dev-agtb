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
 * $Id: index.php 40493 2008-10-13 21:10:05Z jmertic $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
global $theme;


require_once('modules/Forecasts/ForecastUtils.php');
global $mod_strings;

// BEGIN SUGARINTERNAL CUSTOMIZATION: provide lance with access to see camerons forecasts
$tmp_user = null;
if($GLOBALS['current_user']->user_name == 'lkaji' || $GLOBALS['current_user']->user_name == 'msunder' || $GLOBALS['current_user']->user_name == 'tdaimee'){
	$tmp_user = $GLOBALS['current_user'];
	// Larry's user
	$GLOBALS['current_user']->retrieve('21c60e95-5fcd-c0c7-d871-4a021699a004');
}
// END SUGARINTERNAL CUSTOMIZATION: provide lance with access to see camerons forecasts
if (!empty($_REQUEST['page'])) {
    $user = new User();
    $user->retrieve($user,$_REQUEST['page']);
    echo get_chart_for_user($current_user,$_REQUEST['forecast_type']);
} else {
    include ('modules/Forecasts/DetailView.php');
}

// BEGIN SUGARINTERNAL CUSTOMIZATION: provide lance with access to see camerons forecasts
if(!is_null($tmp_user)){
	unset($GLOBALS['current_user']);
	$GLOBALS['current_user'] = $tmp_user;
	echo "<script>\n document.CommitEditView.elements['saveworksheet'].disabled = 'disabled'; \n</script>\n";
	echo "<script>\n document.CommitEditView.elements['resetworksheet'].disabled = 'disabled'; \n</script>\n";
	echo "<script>\n document.CommitEditView.elements['getchart'].disabled = 'disabled'; \n</script>\n";
	echo "<script>\n document.CommitEditView.elements['rollupcommit'].disabled = 'disabled'; \n</script>\n";
}
// END SUGARINTERNAL CUSTOMIZATION: provide lance with access to see camerons forecasts
?>
