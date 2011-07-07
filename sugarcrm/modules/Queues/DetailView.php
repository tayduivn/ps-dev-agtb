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
require_once('modules/Queues/Queue.php');
require_once('include/DetailView/DetailView.php');

global $mod_strings;
global $app_strings;
global $sugar_config;
global $timedate;
global $theme;

/* start standard DetailView layout process */
$GLOBALS['log']->info("Queues DetailView");
$focus = new Queue();
$detailView = new DetailView();
$offset=0;
if (isset($_REQUEST['offset']) or isset($_REQUEST['record'])) {
	$result = $detailView->processSugarBean("QUEUE", $focus, $offset);
	if($result == null) {
	    sugar_die($app_strings['ERROR_NO_RECORD']);
	}
	$focus=$result;
} else {
	header("Location: index.php?module=Queues&action=index");
}
if(isset($_REQUEST['isDuplicate']) && $_REQUEST['isDuplicate'] == 'true') {
	$focus->id = "";
}

$focus->getQueues();
$parent = '';
$child = '';
foreach($focus->parent_ids as $k => $id) {
	if(empty($this->db)) {
		$this->db = DBManagerFactory::getInstance();	
	}
	$r = $this->db->query("SELECT id, name FROM queues WHERE id = '".$id."'");
	$a = $this->db->fetchByAssoc($r);
	$parent .= "<a href='".$sugar_config['site_url']."/index.php?module=Queues&action=DetailView&record=".$id."'>".$a['name']."</a><br>";
}
foreach($focus->child_ids as $k => $id) {
	if(empty($this->db)) {
		$this->db = DBManagerFactory::getInstance();	
	}
	$r = $this->db->query("SELECT id, name FROM queues WHERE id = '".$id."'");
	$a = $this->db->fetchByAssoc($r);
	$child .= "<a href='".$sugar_config['site_url']."/index.php?module=Queues&action=DetailView&record=".$id."'>".$a['name']."</a><br>";
}

echo get_module_title($mod_strings['LBL_MODULE_NAME'], $mod_strings['LBL_MODULE_NAME'].": ".$focus->name, true);

/* end standard DetailView layout process */

/* start custom value assignments */
$allWorkflows = $focus->getWorkflows();
if(!empty($focus->workflows)) {
	$workflows = $allWorkflows[$focus->workflows]['name'];
} else {
	$workflows = $mod_strings['LBL_NONE'];
}

/* end custom value assignments */

$xtpl = new XTemplate('modules/Queues/DetailView.html');
// custom assigns

$xtpl->assign('MOD', $mod_strings);
$xtpl->assign('APP', $app_strings);
$xtpl->assign("CREATED_BY", $focus->created_by_name);
$xtpl->assign("MODIFIED_BY", $focus->modified_by_name);
$xtpl->assign("GRIDLINE", $gridline);
$xtpl->assign("PRINT_URL", "index.php?".$GLOBALS['request_string']);
$xtpl->assign("ID", $focus->id);
$xtpl->assign('NAME', $focus->name);
$xtpl->assign('STATUS', $focus->status);
$xtpl->assign('PARENTS', $parent);
$xtpl->assign('CHILDREN', $child);
$xtpl->assign('WORKFLOWS', $workflows);
$xtpl->assign('QUEUEDITEMS', $focus->queuedItems);
$xtpl->parse('main');
$xtpl->out('main');

require_once('include/SubPanel/SubPanelTiles.php');
$subpanel = new SubPanelTiles($focus, 'Queues');
echo $subpanel->display();
?>
