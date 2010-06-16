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

require_once('modules/Posts/Post.php');

/*
Including thread.php so I can pull parent forum to make sure that
the parent's 'recent_post_title' isn't this one. If it is,
we have to remove that from the parent thread so there is no
reference to this thread
*/
require_once('modules/Threads/Thread.php');

if(!ACLController::checkAccess('Posts', 'delete', true)){
    ACLController::displayNoAccess(false);
    sugar_cleanup(true);
}

$focus = new Post();

if(!isset($_REQUEST['record']))
	sugar_die("A record number must be specified to delete the post.");

if(!is_admin($current_user))
{
	die('Only administrators can delete a Post');
}
	
$focus->retrieve($_REQUEST['record']);
if(!$focus->ACLAccess('Delete')){
	ACLController::displayNoAccess(true);
	sugar_cleanup(true);
}
$focus->mark_deleted($_REQUEST['record']);


// decrements values as applicable
if(!empty($focus->thread_id))
{
  require_once('modules/Threads/Thread.php');

  $parent_thread = new Thread();
  $parent_thread->retrieve($focus->thread_id);
  $parent_thread->decrementPostCount();
}


header("Location: index.php?module=".$_REQUEST['return_module']."&action=".$_REQUEST['return_action']."&record=".$_REQUEST['return_id']);
?>
