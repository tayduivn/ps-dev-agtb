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
/*********************************************************************************
 * $Id: Delete.php 13782 2006-06-06 17:58:55 +0000 (Tue, 06 Jun 2006) majed $
 * Description:  Deletes an Account record and then redirects the browser to the
 * defined return URL.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
global $mod_strings;
global $sugar_config;

if(!isset($_REQUEST['record']))
	sugar_die($mod_strings['ERR_DELETE_RECORD']);
$focus = BeanFactory::getBean('KBOLDDocuments', $_REQUEST['record']);
if(!$focus->ACLAccess('Delete')){
	ACLController::displayNoAccess(true);
	sugar_cleanup(true);
}

//Retrieve all related kbolddocument revisions.
$kbdocrevs = KBOLDDocument::get_kbolddocument_revisions($_REQUEST['record']);
//Loop through kbolddocument revisions and delete one by one.
if (!empty($kbdocrevs) && is_array($kbdocrevs)) {
	foreach($kbdocrevs as $key=>$thiskbid) {
		$thiskbversion = BeanFactory::getBean('KBOLDDocumentRevisions', $thiskbid);
		//Check for related documentrevision and delete.
        if($thiskbversion->document_revision_id != null){
	        $docrev_id = $thiskbversion->document_revision_id;
			$thisdocrev = BeanFactory::getBean('DocumentRevisions', $docrev_id);

           	UploadFile::unlink_file($docrev_id,$thisdocrev->filename);
           	UploadFile::unlink_file($docrev_id);
			//mark version deleted
			$thisdocrev->mark_deleted($thisdocrev->id);
        }
        //Also check for related kboldcontent and delete.
        if($thiskbversion->kboldcontent_id != null){
			BeanFactory::deleteBean('KBOLDContents', $thiskbversion->kboldcontent_id);
        }
		//Finally delete the kbolddocument revision.
	   $thiskbversion->mark_deleted($thiskbversion->id);
	}
}

//delete kbolddocuments_kboldtags
$deleted=1;
$q = 'UPDATE kbolddocuments_kboldtags SET deleted = '.$deleted.' WHERE kbolddocument_id = \''.$_REQUEST['record'].'\'';
$focus->db->query($q);

$focus->mark_deleted($_REQUEST['record']);

header("Location: index.php?module=".$_REQUEST['return_module']."&action=".$_REQUEST['return_action']."&record=".$_REQUEST['return_id']);
