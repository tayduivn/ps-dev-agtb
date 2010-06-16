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
 * $Id: Save.php 45763 2009-04-01 19:16:18Z majed $
 * Description:  Base Form For Notes
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
 



require_once('include/formbase.php');
require_once('include/upload_file.php');

global $mod_strings;
$mod_strings = return_module_language($current_language, 'DocumentRevisions');

$prefix='';

$do_final_move = 0;

$Revision = new DocumentRevision();
$Document = new Document();
if (isset($_REQUEST['record'])) {
	$Document->retrieve($_REQUEST['record']);
}
if(!$Document->ACLAccess('Save')){
		ACLController::displayNoAccess(true);
		sugar_cleanup(true);
}
if (isset($_REQUEST['SaveRevision'])) {
	
	//fetch the document record.
	$Document->retrieve($_REQUEST['return_id']);
	
	if($useRequired &&  !checkRequired($prefix, array_keys($Revision->required_fields))){
		return null;
	}

	$Revision = populateFromPost($prefix, $Revision);
	$upload_file = new UploadFile('uploadfile');
	if (isset($_FILES['uploadfile']) && $upload_file->confirm_upload())
	{
        $Revision->filename = $upload_file->get_stored_file_name();
        $Revision->file_mime_type = $upload_file->mime_type;
		$Revision->file_ext = $upload_file->file_ext;
  	 	  	 	
  	 	$do_final_move = 1;
	}
	
	//save revision
	$Revision->document_id = $_REQUEST['return_id'];
	$Revision->id = null;  // 17767: Security fix, make sure no id is passed in via form.
	$Revision->save();

	//revsion is the document.	
	$Document->document_revision_id = $Revision->id;
	$Document->save();
	$return_id = $Document->id;
} 

if ($do_final_move)
{
   	 $upload_file->final_move($Revision->id);
}
else if ( ! empty($_REQUEST['old_id']))
{
   	 $upload_file->duplicate_file($_REQUEST['old_id'], $Revision->id, $Revision->filename);
}

$GLOBALS['log']->debug("Saved record with id of ".$return_id);
handleRedirect($return_id, "Documents");
?>
