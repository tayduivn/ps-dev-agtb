<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
/*********************************************************************************
 * $Id: BindMapView.php 53846 2010-01-19 20:17:43Z jmertic $
 * Description:  
 ********************************************************************************/





$header_text = '';
global $mod_strings;
global $app_list_strings;
global $app_strings;
global $current_user;

$focus = new CustomQuery();

	if(isset($_REQUEST['record']) && isset($_REQUEST['record'])) {
    	$focus->retrieve($_REQUEST['record']);
	}
	
	if(isset($_REQUEST['old_column_array']) && $_REQUEST['old_column_array']!="") {
		$old_column_array = $_REQUEST['old_column_array'];
	}


if (!is_admin($current_user))
{
   sugar_die($app_strings['LBL_UNAUTH_ADMIN']);
}

global $theme;


$GLOBALS['log']->info("DataSets edit view");

$xtpl=new XTemplate ('modules/CustomQueries/BindMapView.html');
$xtpl->assign("MOD", $mod_strings);
$xtpl->assign("APP", $app_strings);

	$temp_select = $focus->repair_column_binding();
	$temp_select2 = $temp_select;

	foreach($_SESSION['old_column_array'] as $key => $value){
		//eliminate direct matches
		if(!empty($temp_select2[$value])){
				unset($temp_select2[$value]);
		//end eliminate direct matches
		}
	//foreach	
	}	

	foreach($_SESSION['old_column_array'] as $key => $value){
		//only show if there is no direct match
		if(empty($temp_select[$value])){
			$selectdropdown = get_select_options_with_id($temp_select2,$value);
			$xtpl->assign("OLD_COLUMN_NAME", $value);
			$xtpl->assign("SELECT_NAME","column_".$key);
			$xtpl->assign("SELECT_OPTIONS",$selectdropdown);
			$xtpl->parse("main.row");
		//end if only show if no direct match
		} else {
			//remove this element from the array
			unset($temp_select[$value]);	
		}	

	//foreach	
	}	
	
	$xtpl->assign("ID", $_REQUEST['record']);

	$xtpl->parse("main");
	$xtpl->out("main");

?>
