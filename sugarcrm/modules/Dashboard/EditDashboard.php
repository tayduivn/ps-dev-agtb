<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************

 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/


global $mod_strings;



$focus = new Dashboard();

if(!isset($_REQUEST['chart_index']))
	sugar_die('no index is requested to move');

if(!isset($_REQUEST['record']))
	sugar_die('no index is requested to move');

global $current_user;

$focus->retrieve($_REQUEST['record']);

if ( empty($focus->id) || $focus->id == -1)
{
	sugar_die("there is no dashboard associated to this id:".$_REQUEST['record']);
}

if ( $current_user->id != $focus->assigned_user_id)
{
	sugar_die("why are you trying to edit someone else's dashboard?");
}

if ( $_REQUEST['dashboard_action'] == 'move_up')
{
	$focus->move('up',$_REQUEST['chart_index']);
} else if ($_REQUEST['dashboard_action'] == 'move_down')
{
  $focus->move('down',$_REQUEST['chart_index']);
} else if ($_REQUEST['dashboard_action'] == 'delete')
{   
	$focus->delete($_REQUEST['chart_index']);
} else if ($_REQUEST['dashboard_action'] == 'add')
{   
	$focus->add($_REQUEST['chart_type'],$_REQUEST['chart_id'],$_REQUEST['chart_index']);
}
else if ($_REQUEST['dashboard_action'] == 'arrange')
{   
	$focus->arrange(explode('-',$_REQUEST['chartorder']));
}
header("Location: index.php?module=".$_REQUEST['return_module']."&action=".$_REQUEST['return_action']."&record=".$_REQUEST['return_id']);

exit;
?>
