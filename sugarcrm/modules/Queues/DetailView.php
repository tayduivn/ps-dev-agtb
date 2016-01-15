<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
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

echo getClassicModuleTitle($mod_strings['LBL_MODULE_NAME'], array($mod_strings['LBL_MODULE_NAME'],$focus->name), true);

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
