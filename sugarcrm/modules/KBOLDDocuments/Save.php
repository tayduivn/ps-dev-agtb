<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
require_once('include/formbase.php');
require_once('include/upload_file.php');
require_once('modules/KBOLDDocuments/Forms.php');

global $mod_strings, $timedate;
$mod_strings = return_module_language($current_language, 'KBOLDDocuments');

$prefix='';
$do_final_move = 0;

$KBOLDDocument = BeanFactory::getBean('KBOLDDocuments');
$KBRevision = BeanFactory::getBean('KBOLDDocumentRevisions');
if (isset($_REQUEST['record'])) {
	$KBOLDDocument->retrieve($_REQUEST['record']);
}

if(!$KBOLDDocument->ACLAccess('Save')){
		ACLController::displayNoAccess(true);
		sugar_cleanup(true);
}

$KBOLDDocument = populateFromPost('', $KBOLDDocument);
//set check_notify flag
$check_notify = false;

if (!empty($KBOLDDocument->case_id)) {
    $KBOLDDocument->parent_id = $KBOLDDocument->case_id;
    $KBOLDDocument->parent_type = "Cases";
}

//BEGIN SUGARCRM flav=ent ONLY
if (!isset($_POST['is_external'])) $KBOLDDocument->is_external_article = 0;
else $KBOLDDocument->is_external_article = 1;
//END SUGARCRM flav=ent ONLY

if (isset($KBOLDDocument->id)) {
    //retrieve the existing document before saving the current
    $old_Id = $KBOLDDocument->id;
    $oldKB = BeanFactory::getBean('KBOLDDocuments', $old_Id);
    //check if status has changed
    if($oldKB->status_id != $KBOLDDocument->status_id){
       //check if status was draft or in review
        if(($oldKB->status_id == 'Draft' && $KBOLDDocument->status_id=='In Review') ||
           ($oldKB->status_id == 'In Review' && $KBOLDDocument->status_id=='Draft') ||
           ($oldKB->status_id == 'Published')){
	    	$check_notify = true;
	    }
	    if($KBOLDDocument->status_id == 'Published'){
	    	$check_notify = true;
	    	//also set the published date if it's null or empty
	    	if(empty($KBOLDDocument->active_date) || $KBOLDDocument->active_date==null){
	            $KBOLDDocument->active_date = $timedate->nowDate();
	    	}
	    	if(empty($KBOLDDocument->kbdoc_approver_id) || $KBOLDDocument->kbdoc_approver_id==null){
	            $KBOLDDocument->kbdoc_approver_id = $current_user->id;
	    	}
	    }
	    if($KBOLDDocument->status_id != 'Published'){
	    	//also set the published date if it's null or empty
	    	if(!empty($KBOLDDocument->active_date) || $KBOLDDocument->active_date!=null){
	            $KBOLDDocument->active_date = '';
	    	}
	    }
	    if($KBOLDDocument->status_id == 'In Review'){
		    if(empty($KBOLDDocument->kbdoc_approver_id) || $KBOLDDocument->kbdoc_approver_id==null){
		            $KBOLDDocument->kbdoc_approver_id = $current_user->id;
		    	}
	    }
    }

  //save document tags

  $KBOLDDocument->save($check_notify);
  $return_id = $KBOLDDocument->id;
  $KBRevision->retrieve($KBOLDDocument->kbolddocument_revision_id);
  //update the content
  $KBOLDContent = BeanFactory::getBean('KBOLDContents', $KBRevision->kboldcontent_id);
  $KBOLDContent->team_id = $KBOLDDocument->team_id;
  if(strpos(getenv('HTTP_USER_AGENT'), 'MSIE')){
      $KBOLDContent->kbolddocument_body = $_REQUEST['body_html'];
  } else{
      $article_body = '';
      $url_arr = parse_url($sugar_config['site_url']);
      $article_body = str_replace($sugar_config['site_url'].'/cache/images/', $url_arr['path'].'/cache/images/', $_REQUEST['body_html']);
      $article_body = str_replace($url_arr['path'].'/cache/images/', $sugar_config['site_url'].'/cache/images/', $article_body);
      $KBOLDContent->kbolddocument_body = $article_body;
  }
  $KBOLDContent->save();
  //save tags
	if(isset($_REQUEST['docTagIds'])  && $_REQUEST['docTagIds'] != null){
		for($i=0;$i<count($_REQUEST['docTagIds']);$i++){
			if(isset($_REQUEST['docTagIds'][$i]) && !empty($_REQUEST['docTagIds'][$i])) {
	           $KBOLDDocumentKBOLDTag = BeanFactory::getBean('KBOLDDocumentKBOLDTags');
			   $KBOLDDocumentKBOLDTag->kboldtag_id = $_REQUEST['docTagIds'][$i];
			   $KBOLDDocumentKBOLDTag->kbolddocument_id = $KBOLDDocument->id;
			   $KBOLDDocumentKBOLDTag->team_id = $KBOLDDocument->team_id;
			   $KBOLDDocumentKBOLDTag->save();
			}
		}
	}

	//also update the already saved kbolddocuments_kboldtags team_id
	$KBOLDDocumentKBOLDTag = BeanFactory::getBean('KBOLDDocumentKBOLDTags');
	$q = 'UPDATE kbolddocuments_kboldtags SET team_id = \''.$KBOLDDocument->team_id.'\' WHERE kbolddocument_id = \''.$KBOLDDocument->id.'\'';
	$KBOLDDocumentKBOLDTag->db->query($q);
}
else {
	  if($KBOLDDocument != null){
		if($KBOLDDocument->status_id == 'In Review' || $KBOLDDocument->status_id == 'Published'){

			$check_notify = true;
			if(empty($KBOLDDocument->kbdoc_approver_id) || $KBOLDDocument->kbdoc_approver_id==null){
	            $KBOLDDocument->kbdoc_approver_id = $current_user->id;
	    	}
		}
		if($KBOLDDocument->status_id == 'Published'){
	    	//set the published date if it's null or empty
	    	if(empty($KBOLDDocument->active_date) || $KBOLDDocument->active_date==null){
	            $KBOLDDocument->active_date = $timedate->nowDate();

	    	}
	    }
	    if($KBOLDDocument->status_id != 'Published'){
	    	//also set the published date if it's null or empty
	    	if(!empty($KBOLDDocument->active_date) || $KBOLDDocument->active_date!=null){
	            $KBOLDDocument->active_date = '';
	    	}
	    }
	  }

	//save kbolddocument first
	  $kb_id = create_guid();
	  $KBRevision->change_log = $mod_strings['DEF_CREATE_LOG'];
	  $KBRevision->revision = $KBOLDDocument->revision;
	  $KBRevision->kbolddocument_id = $kb_id;
	  $KBRevision->latest = true;
	  $KBRevision->save();
	//save tags
		if(isset($_REQUEST['docTagIds']) && $_REQUEST['docTagIds'] != null){
			for($i=0;$i<count($_REQUEST['docTagIds']);$i++){
				if(isset($_REQUEST['docTagIds'][$i]) && !empty($_REQUEST['docTagIds'][$i])) {
		           $KBOLDDocumentKBOLDTag = BeanFactory::getBean('KBOLDDocumentKBOLDTags');
				   $KBOLDDocumentKBOLDTag->kboldtag_id = $_REQUEST['docTagIds'][$i];
				   $KBOLDDocumentKBOLDTag->kbolddocument_id = $kb_id;
				   $KBOLDDocumentKBOLDTag->team_id = $KBOLDDocument->team_id;
				   $KBOLDDocumentKBOLDTag->save();
				}
			}
		}
		if($_REQUEST['body_html'] != null){
		    $DocRevision = BeanFactory::getBean('DocumentRevisions');
			$KBOLDContent = BeanFactory::getBean('KBOLDContents');
			//relate doc revision and kbdoc revision
			$DocRevision->filename = $KBOLDDocument->kbolddocument_name;
			$DocRevision->save();
		    //relate doc revision to kboldcontent
			$KBOLDContent->document_revision_id = $DocRevision->id;
			$KBOLDContent->team_id = $KBOLDDocument->team_id;
			if(strpos(getenv('HTTP_USER_AGENT'), 'MSIE')){
			    $KBOLDContent->kbolddocument_body = $_REQUEST['body_html'];
			} else{
				$article_body = '';
				$url_arr = parse_url($sugar_config['site_url']);
	            $article_body = str_replace($sugar_config['site_url'].'/cache/images/', $url_arr['path'].'/cache/images/', $_REQUEST['body_html']);
            	$article_body = str_replace($url_arr['path'].'/cache/images/', $sugar_config['site_url'].'/cache/images/', $article_body);
	            $KBOLDContent->kbolddocument_body = $article_body;
			}
			$KBOLDContent->save();
		}

		//update document with document revision

	    //save all the attachments as documents and link them to the kbolddocument


	    //update the kbolddocument revision with document revision and content
		$KBRevision->kboldcontent_id = $KBOLDContent->id;
		$KBRevision->document_revision_id = $DocRevision->id;
		$KBRevision->save();
		//update kbolddocument with kbolddocument revision id
		$KBOLDDocument->id = $kb_id;
		$KBOLDDocument->new_with_id = true;
		$KBOLDDocument->kbolddocument_revision_id = $KBRevision->id;
		$return_id = $KBOLDDocument->save($check_notify);
}




if (!isset($_POST[$prefix.'is_template'])) $KBOLDDocument->is_template = 0;
else $KBOLDDocument->is_template = 1;

$upload_file = new UploadFile('uploadfile');
$do_final_move = 0;

//loop through all the attachments and convert them into documents
//also take the KBOLDDocumentbody and convert into a document
$file_uploaded_count = count($_FILES);
//array of removed files
$files = explode(",", $_REQUEST['removed_files']);

for($i = 0; $i < $file_uploaded_count; $i++) {
	$found = false;
	foreach($files as $file){
		if($file == $_FILES['kbdoc_attachment'.$i]['name']){
			$found = true;
			break;
		}
	}
	if($found){
		//do nothing
	}
	else{
		$upload_file = new UploadFile('kbdoc_attachment'.$i);

		if($upload_file == null || $_FILES['kbdoc_attachment'.$i]['size']==0) {
			continue;
		}
	    $DocRevision = BeanFactory::getBean('DocumentRevisions');
	    if(isset($_FILES['kbdoc_attachment'.$i]) && $upload_file->confirm_upload()) {

		     //prepare document revision
		    $DocRevision->filename = $upload_file->get_stored_file_name();
		    $DocRevision->file_mime_type = $upload_file->mime_type;
			$DocRevision->file_ext = $upload_file->file_ext;
		 	$DocRevision->save();

		 	//save kbrvision
		    $KBRevisionAtts = BeanFactory::getBean('KBOLDDocumentRevisions');
		 	$KBRevisionAtts->change_log = $mod_strings['DEF_CREATE_LOG'];
		    $KBRevisionAtts->revision = $KBOLDDocument->revision;
		    $KBRevisionAtts->kbolddocument_id = $KBOLDDocument->id;
		    $KBRevisionAtts->document_revision_id = $DocRevision->id;
		    //$KBRevisionAtts->latest = true;
		    $KBRevisionAtts->save();

		 	//update document with revision id
		 	//$Document->document_revision_id = $DocRevision->id;

		 	//$Document->save();

		 	$do_final_move = 1;
		 	if ($do_final_move) {
	  	      $upload_file->final_move($DocRevision->id);
	        }
	        else if ( ! empty($_REQUEST['old_id'])) {
	   	      $upload_file->duplicate_file($_REQUEST['old_id'], $DocRevision->id, $DocRevision->filename);
	        }
	   }
  }
}

if(isset($_REQUEST['removed_tags']) && !empty($_REQUEST['removed_tags'])){
	$tags = explode(",", $_REQUEST['removed_tags']);
	$deleted = 1;
	foreach($tags as $tag_id){
		$tag_id= trim($tag_id);
		if(!empty($tag_id)){
			$KBOLDDocumentKBOLDTag = BeanFactory::getBean('KBOLDDocumentKBOLDTags');
			$q = 'UPDATE kbolddocuments_kboldtags SET deleted = \''.$deleted.'\' WHERE kboldtag_id = \''.$tag_id.'\' and kbolddocument_id = \''.$KBOLDDocument->id.'\'';
			$KBOLDDocumentKBOLDTag->db->query($q);
		}
	}

}
if(isset($_REQUEST['removed_attachments']) && !empty($_REQUEST['removed_attachments'])){
	$atts = explode(",", $_REQUEST['removed_attachments']);
	$deleted = 1;
	foreach($atts as $docrev_id){
		$docrev_id = trim($docrev_id);
		if(!empty($docrev_id)){
			$DocumentRevision = BeanFactory::getBean('DocumentRevisions');
			$q = 'UPDATE document_revisions SET deleted = '.$deleted.' WHERE id = \''.$docrev_id.'\'';
			$DocumentRevision->db->query($q);
		}
	}
}


//$return_id = $KBOLDDocument->id;
$GLOBALS['log']->debug("Saved record with id of ".$return_id);
handleRedirect($return_id, "KBOLDDocuments");

?>
