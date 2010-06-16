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

require_once('modules/Threads/Thread.php');

/*
Including forum.php so I can pull parent forum to make sure that
the parent's 'recent_thread_title' isn't this one. If it is,
we have to remove that from the parent forum so there is no
reference to this thread
*/
require_once('modules/Forums/Forum.php');

if(!ACLController::checkAccess('Threads', 'delete', true)){
    ACLController::displayNoAccess(false);
    sugar_cleanup(true);
}

$focus = new Thread();

// creating the forum
$focusParentForum = new Forum();

if(!isset($_REQUEST['record']))
	sugar_die("A record number must be specified to delete the thread.");

if(!is_admin($current_user))
{
	die('Only administrators can delete a Thread');
}
	
$focus->retrieve($_REQUEST['record']);
if(!$focus->ACLAccess('Delete')){
	ACLController::displayNoAccess(true);
	sugar_cleanup(true);
}

$focus->mark_deleted($_REQUEST['record']);

// pull in forum info
$focusParentForum->retrieve($focus->forum_id);

// if condition passing means the parent forum's most recent thread was this one
// SOOOO, we have to set it to inform the user that that thread was deleted
if(!strcmp ( $focus->id, $focusParentForum->recent_thread_id ))
{
  $GLOBALS['db']->query(
    "update forums ".
    "set recent_thread_title='".$GLOBALS['db']->quote("Thread was deleted by administrator")."', ".
    "recent_thread_id=0 ".
    "where id='".$GLOBALS['db']->quote($focusParentForum->id)."'"
  );
}

// we also have to mark all child posts as deleted
$result = $GLOBALS['db']->query(
  "select id ".
  "from posts ".
  "where thread_id='".$GLOBALS['db']->quote($focus->id)."' "
);

require_once('modules/Posts/Post.php');

while($row = $focus->db->fetchByAssoc($result))
{
  $child_post = new Post();
  $child_post->mark_deleted($row['id']);
  
  $focus->decrementPostCount();
}

if(!empty($focus->forum_id))
{
  require_once('modules/Forums/Forum.php');
  $parent_forum = new Forum();
  $parent_forum->retrieve($focus->forum_id);
  $parent_forum->decrementThreadCount();
}

header("Location: index.php?module=".$_REQUEST['return_module']."&action=".$_REQUEST['return_action']."&record=".$_REQUEST['return_id']);
?>
