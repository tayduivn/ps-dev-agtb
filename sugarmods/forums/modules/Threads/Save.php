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
//echo "<pre>"; print_r($_POST); echo "</pre>"; die();

require_once('modules/Threads/Thread.php');
require_once('include/utils.php');
global $current_user;

$focus =& new Thread();

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
foreach ($focus->additional_column_fields as $field){
	if (isset($_POST[$field])) {
		$focus->$field=$_POST[$field];		
	}
}

if(!empty($focus->id)){
	if (isset($_POST['is_sticky']) && ($_POST['is_sticky'] == '1' || $_POST['is_sticky'] == 'on'))
		$focus->is_sticky = 1;
	else
		$focus->is_sticky = 0;
}
else{
	if (isset($_POST['is_sticky']) && $_POST['is_sticky'] == 'on')
		$focus->is_sticky = 1;
}

$html = trim($focus->description_html);
if(!empty($html)) {
  if(false === stristr($html, '&lt;html')) {
    $focus->description_html = $html;
  }
}


/*
echo "<PRE>";
print_r($_POST);
print_r($focus);
echo "</PRE>";
die('');
*/

$new_thread = false;
if(empty($focus->id))
  $new_thread = true;

$focus->save();
		
$return_module = (!empty($_POST['return_module'])) ? $_POST['return_module'] : "Forums";
$return_action = (!empty($_POST['return_action'])) ? $_POST['return_action'] : "DetailView";

$return_id = $focus->id;
if($return_module == "Forums" && !empty($focus->forum_id))
  $return_id = $focus->forum_id;
else if(!empty($_POST['return_id']))
  $return_id = $_POST['return_id'];

// increments if this was a new thread created
if($new_thread && !empty($focus->forum_id))
{
  require_once('modules/Forums/Forum.php');
  $parent_forum = new Forum();
  $parent_forum->retrieve($focus->forum_id);
  $parent_forum->incrementThreadCount();
}

header("Location: index.php?action={$return_action}&module={$return_module}&record={$return_id}");	

?>
