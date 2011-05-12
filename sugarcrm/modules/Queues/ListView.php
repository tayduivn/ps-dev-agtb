<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
//FILE SUGARCRM flav=int ONLY
global $theme;
//_pp($_SESSION);
require_once('modules/Queues/Queue.php');





$focus = new Queue();
global $current_user;
if(is_admin($current_user) && $_REQUEST['module'] != 'DynamicLayout' && !empty($_SESSION['editinplace'])){	
	$ListView->setHeaderText("<a href='index.php?action=index&module=DynamicLayout&from_action=ListView&from_module=".$_REQUEST['module'] ."'>".SugarThemeRegistry::current()->getImage("EditLayout","border='0' align='bottom'",null,null,'.gif',$mod_strings['LBL_EDITLAYOUT'])."</a>" );
}


$where = '';
$limit = '0';
$orderBy = 'queue_type';
$varName = $focus->object_name;
$allowByOverride = true;

$listView = new ListView();
$listView->initNewXTemplate('modules/Queues/ListView.html', $mod_strings);
$listView->setHeaderTitle($mod_strings['LBL_LIST_FORM']);
$listView->setQuery($where, $limit, $orderBy, 'QUEUE', $allowByOverride);
$listView->xTemplateAssign("REMOVE_INLINE_PNG", SugarThemeRegistry::current()->getImage('delete_inline','align="absmiddle" border="0"',null,null,'.gif',$app_strings['LNK_REMOVE']));
$listView->processListView($focus, "main", "QUEUE");

?>
