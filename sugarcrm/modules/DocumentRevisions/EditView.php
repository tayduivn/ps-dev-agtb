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
 * $Id: EditView.php 54699 2010-02-22 17:09:23Z jmertic $
 * Description: TODO:  To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/upload_file.php');
require_once('modules/DocumentRevisions/Forms.php');

global $app_strings;
global $app_list_strings;
global $mod_strings;
global $current_user;

$focus = new DocumentRevision();

if(isset($_REQUEST['record'])) {
    $focus->retrieve($_REQUEST['record']);
}
$old_id = '';

if(isset($_REQUEST['isDuplicate']) && $_REQUEST['isDuplicate'] == 'true') 
{
	if (! empty($focus->filename) )
	{	
	 $old_id = $focus->id;
	}
	$focus->id = "";
}

echo get_module_title('DocumentRevisions', $mod_strings['LBL_MODULE_NAME'].": ".$focus->document_name, true); 


$GLOBALS['log']->info("Document revision edit view");

$xtpl=new XTemplate ('modules/DocumentRevisions/EditView.html');
$xtpl->assign("MOD", $mod_strings);
$xtpl->assign("APP", $app_strings);

if (isset($_REQUEST['return_module'])) $xtpl->assign("RETURN_MODULE", $_REQUEST['return_module']);
if (isset($_REQUEST['return_action'])) $xtpl->assign("RETURN_ACTION", $_REQUEST['return_action']);
if (isset($_REQUEST['return_id'])) $xtpl->assign("RETURN_ID", $_REQUEST['return_id']);
$xtpl->assign("PRINT_URL", "index.php?".$GLOBALS['request_string']);
$xtpl->assign("JAVASCRIPT", get_set_focus_js().get_validate_record_document_revision_js());

$doc_type_options = get_select_options_with_id($app_list_strings[$focus->field_defs['doc_type']['options']], $_REQUEST['doc_type']);

$xtpl->assign("DOC_TYPE_OPTIONS", $doc_type_options);
$focus->fill_document_name_revision($_REQUEST['return_id']);

$xtpl->assign("ID", $focus->id);
$xtpl->assign("DOCUMENT_NAME",$_REQUEST['document_name']);
$xtpl->assign("CURRENT_REVISION",$_REQUEST['document_revision']);

if($_REQUEST['document_revision'] == null) {
	$xtpl->assign("CURRENT_REVISION",$focus->latest_revision);
}

$doc_revision = new DocumentRevision();
$doc_revision->retrieve($_REQUEST['document_revision_id']);
$file_url = 'index.php?entryPoint=download&id='.$doc_revision->id.'&type=Documents';

if($doc_revision->doc_type!='Sugar' && !empty($doc_revision->doc_url)) {
	$file_url = $doc_revision->doc_url;
}

$xtpl->assign("FILE_URL", $file_url);


$xtpl->parse("main");
$xtpl->out("main");

//implements required fields check based on the required fields list defined in the bean.

$javascript = new javascript();
$javascript->setFormName('DocumentRevisionEditView');
$javascript->setSugarBean($focus);
$javascript->addAllFields('');
echo $javascript->getScript();

?>