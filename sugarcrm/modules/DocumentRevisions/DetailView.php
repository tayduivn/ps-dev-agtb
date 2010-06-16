<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement 
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.  
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may 
 *not use this file except in compliance with the License. Under the terms of the license, You 
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or 
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or 
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit 
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the 
 *Software without first paying applicable fees is strictly prohibited.  You do not have the 
 *right to remove SugarCRM copyrights from the source code or user interface. 
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and 
 * (ii) the SugarCRM copyright notice 
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer 
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.  
 ********************************************************************************/
/*********************************************************************************
 * $Id: DetailView.php 53409 2010-01-04 03:31:15Z roger $
 * Description: TODO:  To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/




require_once('include/upload_file.php');


global $app_strings;
global $app_list_strings;
global $mod_strings;
global $current_user;
global $gridline;
global $locale;

$focus = new DocumentRevision();

if(isset($_REQUEST['record'])) {
    $focus->retrieve($_REQUEST['record']);
}
$old_id = '';

echo get_module_title('DocumentRevisions', $mod_strings['LBL_MODULE_NAME'].": ".$focus->document_name, true); 


$GLOBALS['log']->info("Document revision detail view");

$xtpl=new XTemplate ('modules/DocumentRevisions/DetailView.html');
$xtpl->assign("MOD", $mod_strings);
$xtpl->assign("APP", $app_strings);

if (isset($_REQUEST['return_module'])) $xtpl->assign("RETURN_MODULE", $_REQUEST['return_module']);
if (isset($_REQUEST['return_action'])) $xtpl->assign("RETURN_ACTION", $_REQUEST['return_action']);
if (isset($_REQUEST['return_id'])) $xtpl->assign("RETURN_ID", $_REQUEST['return_id']);
$xtpl->assign("PRINT_URL", "index.php?".$GLOBALS['request_string']);

$focus->fill_document_name_revision($focus->document_id);

$xtpl->assign("ID", $focus->id);
$xtpl->assign("DOCUMENT_NAME",$focus->name);
$xtpl->assign("CURRENT_REVISION",$focus->latest_revision);
$xtpl->assign("CHANGE_LOG",$focus->change_log);
$created_user = new User();
$created_user->retrieve($focus->created_by);
$xtpl->assign("CREATED_BY",$locale->getLocaleFormattedName($created_user->first_name, $created_user->last_name));

$xtpl->assign("DATE_CREATED",$focus->date_entered);
$xtpl->assign("REVISION",$focus->revision);
$xtpl->assign("FILENAME",$focus->filename);

$xtpl->assign("FILE_NAME", $focus->filename);
$xtpl->assign("SAVE_FILE", $focus->id);

$xtpl->assign("FILE_URL", UploadFile::get_url($focus->filename,$focus->id));
$xtpl->assign("GRIDLINE", $gridline);


$xtpl->parse("main");
$xtpl->out("main");
?>