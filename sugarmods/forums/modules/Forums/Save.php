<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-professional-eula.html
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id
 * Description:
 ********************************************************************************/
//_ppd($_POST);
require_once('modules/Forums/Forum.php');
require_once('modules/ForumTopics/ForumTopic.php');
global $current_user;

//included for saving category ranking as well as category -- see below
global $app_list_strings;

$focus = new Forum();

if ($_POST['isDuplicate'] != 1) {
	$focus->retrieve($_POST['record']);
}

$focus->explicit=0;
foreach ($focus->column_fields as $field) {
	if (isset($_POST[$field])) {

		if ($field == 'explicit' && $_POST[$field]=='on') {
			$focus->$field=1;
		} else {
			$focus->$field=$_POST[$field];		
		}
	}
}
foreach ($focus->additional_column_fields as $field) {
	if (isset($_POST[$field])) {
		$focus->$field=$_POST[$field];		
	}
}

$seedForumTopic = new ForumTopic();
$topics = $seedForumTopic->get_topics();
$focus->save();
		
$return_module = (!empty($_POST['return_module'])) ? $_POST['return_module'] : "Forums";
$return_action = (!empty($_POST['return_action'])) ? $_POST['return_action'] : "DetailView";
$return_id = $focus->id;


header("Location: index.php?action={$return_action}&module={$return_module}&record={$return_id}");	

?>
