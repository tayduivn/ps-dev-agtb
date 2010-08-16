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
global $theme, $current_language, $mod_strings;


require_once('modules/Calendar/templates/templates_calendar.php');
require_once('modules/Calendar/Calendar.php');
setlocale( LC_TIME ,$current_language);
if(!ACLController::checkAccess('Calendar', 'list', true)){
	ACLController::displayNoAccess(true);
}

echo get_module_title($mod_strings['LBL_MODULE_NAME'], "<span class='pointer'>&raquo;</span>".$mod_strings['LBL_MODULE_ACTION'], true);

if ( empty($_REQUEST['view']))
{
	$_REQUEST['view'] = 'day';
}

$date_arr = array();

if ( isset($_REQUEST['ts']))
{
	$date_arr['ts'] = $_REQUEST['ts'];
}

if ( isset($_REQUEST['day']))
{

	$date_arr['day'] = $_REQUEST['day'];
}

if ( isset($_REQUEST['month']))
{
	$date_arr['month'] = $_REQUEST['month'];
}

if ( isset($_REQUEST['week']))
{
	$date_arr['week'] = $_REQUEST['week'];
}

if ( isset($_REQUEST['year']))
{
	if ($_REQUEST['year'] > 2037 || $_REQUEST['year'] < 1970)
	{
		print("Sorry, calendar cannot handle the year you requested");
		print("<br>Year must be between 1970 and 2037");
		exit;
	}
	$date_arr['year'] = $_REQUEST['year'];
}

// today adjusted for user's timezone
if(empty($date_arr)) {
	global $timedate;
    $gmt_today = $timedate->get_gmt_db_datetime();
    $user_today = $timedate->handle_offset($gmt_today, $GLOBALS['timedate']->get_db_date_time_format());
	preg_match('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/',$user_today,$matches);

    $date_arr = array(
      'year'=>$matches[1],
      'month'=>$matches[2],
      'day'=>$matches[3],
      'hour'=>$matches[4],
      'min'=>$matches[5]);
} 

$args['calendar'] = new Calendar($_REQUEST['view'], $date_arr);
if ($_REQUEST['view'] == 'day' || $_REQUEST['view'] == 'week' || $_REQUEST['view'] == 'month')
{
	global $current_user;
	$args['calendar']->add_activities($current_user);
}
$args['view'] = $_REQUEST['view'];

?>
<script type="text/javascript" language="JavaScript">
<!-- Begin
function toggleDisplay(id){

	if(this.document.getElementById( id).style.display=='none'){
		this.document.getElementById( id).style.display='inline'
		if(this.document.getElementById(id+"link") != undefined){
			this.document.getElementById(id+"link").style.display='none';
		}
	}else{
		this.document.getElementById(  id).style.display='none'
		if(this.document.getElementById(id+"link") != undefined){
			this.document.getElementById(id+"link").style.display='inline';
		}
	}
}
		//  End -->
	</script>
<table width="100%" border="0" cellpadding="0" cellspacing="0">
<tr>
<td valign=top width="70%" style="padding-right: 10px; padding-top: 2px;">
<?php template_calendar($args); ?>
</td>
<?php if ($_REQUEST['view'] == 'day') { ?>
<td valign=top width="30%">
<?php include("modules/Calendar/TasksListView.php") ;?>
</td>
<?php } ?>
</tr>
</table>
