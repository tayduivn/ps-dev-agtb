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
require_once('XTemplate/xtpl.php');
require_once('data/Tracker.php');
require_once('modules/Threads/Thread.php');
require_once('modules/Threads/Forms.php');
require_once('include/DetailView/DetailView.php');

global $mod_strings;
global $app_strings;
global $app_list_strings;
global $postTitleQuery;

if(!ACLController::checkAccess('Threads', 'view', true)){
    ACLController::displayNoAccess(false);
    sugar_cleanup(true);
}

$focus =& new Thread();
$detailView = new DetailView();
$offset=0;

if (isset($_REQUEST['offset']) or isset($_REQUEST['record'])) {
    $postTitleQuery = $detailView->processSugarBean("THREAD", $focus, $offset);
    if($postTitleQuery == null) {
        sugar_die($app_strings['ERROR_NO_RECORD']);
    }
    $focus=$postTitleQuery;
} else {
    header("Location: index.php?module=Threads&action=index");
}

if(isset($_REQUEST['isDuplicate']) && $_REQUEST['isDuplicate'] == 'true') {
    $focus->id = "";
}
/*
echo "<pre>";
print_r($focus);
echo "</pre>";
*/
$GLOBALS['log']->info("Thread DetailView");

// Increments the view count by 1!!
$focus->increment_view_count();

echo "\n<p>\n";
echo get_module_title($mod_strings['LBL_MODULE_NAME'], $mod_strings['LBL_MODULE_NAME'].": ".$focus->title, true);
echo "\n</p>\n";

global $theme;
$theme_path="themes/".$theme."/";
$image_path=$theme_path."images/";

$GLOBALS['log']->info("Thread detail view");

$xtpl=new XTemplate ('modules/Threads/DetailView.html');
$xtpl->assign("MOD", $mod_strings);
$xtpl->assign("APP", $app_strings);
$xtpl->assign("THEME", $theme);
$xtpl->assign("GRIDLINE", $gridline);
$xtpl->assign("IMAGE_PATH", $image_path); $xtpl->assign("PRINT_URL", "index.php?".$GLOBALS['request_string']);
$xtpl->assign("ID", $focus->id);
$xtpl->assign("FORUM_ID", $focus->forum_id);
$xtpl->assign("RETURN_MODULE", "Threads");
$xtpl->assign("RETURN_ACTION", "DetailView");
$xtpl->assign("ACTION", "EditView");
$xtpl->assign("CREATED_BY", $focus->created_by);
$xtpl->assign("MODIFIED_BY", $focus->modified_by);
$xtpl->assign("CREATED_BY_USER", get_assigned_user_name($focus->created_by));
$xtpl->assign("MODIFIED_BY_USER", get_assigned_user_name($focus->modified_user_id));
$xtpl->assign("DATE_ENTERED", $focus->date_entered);
$xtpl->assign("DATE_MODIFIED", $focus->date_modified);

// set the return values if deleting
if(!empty($focus->forum_id))
{
  $xtpl->assign("DELETE_RETURN_MODULE", "Forums");
  $xtpl->assign("DELETE_RETURN_ACTION", "DetailView");
  $xtpl->assign("DELETE_RETURN_ID", $focus->forum_id);
}
else
{
  $xtpl->assign("DELETE_RETURN_MODULE", "Forums");
  $xtpl->assign("DELETE_RETURN_ACTION", "ListView");
  $xtpl->assign("DELETE_RETURN_ID", "");
}

$xtpl->assign("TITLE", $focus->title);
$desc_html=iconv($app_strings['LBL_CHARSET'],"ISO-8859-1",$focus->description_html);
$desc_html=html_entity_decode($desc_html, ENT_QUOTES, 'ISO-8859-1');
$desc_html=iconv("ISO-8859-1",$app_strings['LBL_CHARSET'],$desc_html);
$xtpl->assign("DESCRIPTION_HTML", $desc_html);


if($focus->is_sticky == '1')
  $xtpl->assign("STICKYDISPLAY", $focus->stickyDisplay);

// get the current user display style
$userDisplayStyle = $current_user->getPreference("threadDisplayStyle", "Forums");

// 'tds' -> (t)hread (d)isplay (s)tyle
if(isset($_REQUEST['tds']))
{
  if($_REQUEST['tds'] == "Threaded")
  {
    $userDisplayStyle = "Threaded";
    $current_user->setPreference("threadDisplayStyle", "Threaded", 0, "Forums");
    $xtpl->assign("ALT_VIEW_STYLE", "<a href=index.php?module=Threads&action=DetailView&record=".$focus->id."&tds=ListView>".$mod_strings['SWITCH_TO_LISTVIEW']);
  }
  else if($_REQUEST['tds'] == "ListView")
  {
    $userDisplayStyle = "ListView";
    $current_user->setPreference("threadDisplayStyle", "ListView", 0, "Forums");
    $xtpl->assign("ALT_VIEW_STYLE", "<a href=index.php?module=Threads&action=DetailView&record=".$focus->id."&tds=Threaded>".$mod_strings['SWITCH_TO_THREADED']);
  }
}
else
{
  if($userDisplayStyle == "Threaded")
    $xtpl->assign("ALT_VIEW_STYLE", "<a href=index.php?module=Threads&action=DetailView&record=".$focus->id."&tds=ListView>".$mod_strings['SWITCH_TO_LISTVIEW']);
  else if($userDisplayStyle == "ListView")
    $xtpl->assign("ALT_VIEW_STYLE", "<a href=index.php?module=Threads&action=DetailView&record=".$focus->id."&tds=Threaded>".$mod_strings['SWITCH_TO_THREADED']);
  else //default to threaded
    $xtpl->assign("ALT_VIEW_STYLE", "<a href=index.php?module=Threads&action=DetailView&record=".$focus->id."&tds=ListView>".$mod_strings['SWITCH_TO_LISTVIEW']);
}

//Add Custom Fields
require_once('modules/DynamicFields/templates/Files/DetailView.php');

if($focus->date_modified != $focus->date_entered || $focus->modified_user_id != $focus->created_by)
  $xtpl->parse("main.modified");

if(is_admin($current_user) || $current_user->id == $focus->created_by)
  $xtpl->parse("main.owner_or_admin");

if(!empty($focus->forum_id))
  $xtpl->parse("main.parent_forum_link");
$xtpl->parse("main");
$xtpl->out("main");

$backup_record_id = $_REQUEST['record'];

if((isset($_REQUEST['tds']) && $_REQUEST['tds'] == "ListView") || $userDisplayStyle == "ListView")
  include('modules/Posts/ListView.php');
else{
    $postTitleQuery = $GLOBALS['db']->query(
                          "select * ".
                            "from posts ".
                            "where thread_id = '".$GLOBALS['db']->quote($focus->id)."' and ".
                            "posts.deleted = 0 ".
                            "order by date_modified "
                          );
    
    $row = $GLOBALS['db']->fetchByAssoc($postTitleQuery);

    if(isset($row['id'])){
      echo "<br>\n<p>\n";
      echo get_module_title('Posts', $mod_strings['LBL_REPLIES_TO_THREAD'], false);
      echo "\n</p>\n";
        $_REQUEST['record'] = $row['id'];
      include('modules/Posts/DetailView.php');
    }
    
    if($row)
    { 
    while ($row = $GLOBALS['db']->fetchByAssoc($postTitleQuery)){
        $_REQUEST['record'] = $row['id'];
    	include('modules/Posts/DetailView.php');
    }
    }
}

$_REQUEST['record'] = $backup_record_id;

?>

<table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td style="padding-bottom: 2px;">
            <form action="index.php" method="post" name="DetailView" id="form">
                <input type="hidden" name="module" value="Threads">
                <input type="hidden" name="record" value="<?php echo $_REQUEST['record']; ?>">
                <input type="hidden" name="isDuplicate" value="0">
                <input type="hidden" name="action" value="EditView">
                <input type="hidden" name="return_module" value="Threads">
                <input type="hidden" name="return_action" value="DetailView">
                <input type="hidden" name="return_id" value="<?php echo $_REQUEST['record']; ?>">
                <td style="padding-bottom: 2px;">
                <INPUT type="button" class="button" value="<?php echo $mod_strings['LBL_REPLY_TO_POST']; ?>" name="<?php echo $_REQUEST['record']; ?>" onClick="window.location='index.php?module=Posts&action=EditView&return_module=Threads&return_action=DetailView&return_id=<?php echo $_REQUEST['record']; ?>'">
                </td>
            </form>
        </td>
    </tr>
</table>
