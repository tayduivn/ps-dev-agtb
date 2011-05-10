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

$focus = new Queue();
$focus->retrieve($_REQUEST['record']);

foreach($focus->column_fields as $field) {
	if(isset($_REQUEST[$field])) {
		$focus->$field = $_REQUEST[$field];
		_pp($field);
	}
}
foreach($focus->additional_column_fields as $field) {
	if(isset($_REQUEST[$field])) {
		$value = $_REQUEST[$field];
		$focus->$field = $value;
	}
}


////////////////////////////////////////////////////
// adds
$p = urldecode($_REQUEST['parent_queues_concat']);
$c = urldecode($_REQUEST['child_queues_concat']);
$pA = array();
$cA = array();
parse_str($p, $pA);
parse_str($c, $cA);
if(!empty($pA['parent_queues'])) {
	$focus->parent_queues = $pA['parent_queues'];
}
if(!empty($cA['child_queues'])) {
	$focus->child_queues = $cA['child_queues'];
}
////////////////////////////////////////////////////
// deletes
$GLOBALS['log']->info('----->Queue now saving self');
$focus->save();

///////////////////////////////////////////////////////////////////////////////
////	RELATIONSHIP SAVES
///////////////////////////////////////////////////////////////////////////////
// hack : delete from queues_queue table
$r = $focus->db->query('DELETE FROM queues_queue WHERE queue_id = "'.$focus->id.'" OR parent_id = "'.$focus->id.'"');
//TODO fix this relationship hack	
 
$GLOBALS['log']->info('----->Queue now saving relationship changes');
$focus->load_relationship('parent_queues');
$focus->load_relationship('child_queues');
$focus->parent_queues->when_dup_relationship_found = 3;//delete on dupe
$focus->child_queues->when_dup_relationship_found = 3;//delete on dupe
//$focus->load_relationship('queues_workflow');
foreach($pA as $key=>$value) {
	$GLOBALS['log']->debug('----->Queue now adding rels of type parent_queues with value: '.$value);
	$focus->parent_queues->add($value);	
}
foreach($cA as $key=>$value) {
	$GLOBALS['log']->debug('----->Queue now adding rels of type child_queues with value: '.$value);
	$focus->child_queues->add($value);
}
//foreach($wA as $key=>$value) {
//	$focus->queues_workflow->add($value);
//}


///////////////////////////////////////////////////////////////////////////////
////	PAGE REDIRECT
///////////////////////////////////////////////////////////////////////////////
$_REQUEST['return_id'] = $focus->id;

$edit='';
if(isset($_REQUEST['return_module']) && $_REQUEST['return_module'] != "") $return_module = $_REQUEST['return_module'];
else $return_module = "Queues";
if(isset($_REQUEST['return_action']) && $_REQUEST['return_action'] != "") $return_action = $_REQUEST['return_action'];
else $return_action = "DetailView";
if(isset($_REQUEST['return_id']) && $_REQUEST['return_id'] != "") $return_id = $_REQUEST['return_id'];
if(!empty($_REQUEST['edit'])) {
	$return_id='';
	$edit='&edit=true';
}

$GLOBALS['log']->debug("Saved record with id of ".$return_id);

$focus->writeToCache($focus);

header("Location: index.php?module=$return_module&action=$return_action&record=$return_id$edit");
?>
