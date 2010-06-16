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

if(empty($_REQUEST['id']) || empty($_REQUEST['type']) || !isset($_SESSION['authenticated_user_id'])) {
	die("Not a Valid Entry Point");
}
else {
    ini_set('zlib.output_compression','Off');//bug 27089, if use gzip here, the Content-Length in hearder may be incorrect.
    // cn: bug 8753: current_user's preferred export charset not being honored
    $GLOBALS['current_user']->retrieve($_SESSION['authenticated_user_id']);
    $GLOBALS['current_language'] = $_SESSION['authenticated_user_language'];
    $app_strings = return_application_language($GLOBALS['current_language']);
    $mod_strings = return_module_language($GLOBALS['current_language'], 'ACL');
    if(!isset($_REQUEST['isTempFile'])) {
	    //Custom modules may have capilizations anywhere in thier names. We should check the passed in format first.
		require('include/modules.php');
		$module = $_REQUEST['type'];
		$file_type = strtolower($_REQUEST['type']);
		if(empty($beanList[$module])) {
			//start guessing at a module name
			$module = ucfirst($file_type);
	    	if(empty($beanList[$module])) {
	       		die($app_strings['ERROR_TYPE_NOT_VALID']);
	    	}
		}
    	$bean_name = $beanList[$module];
	    if(!file_exists('modules/' . $module . '/' . $bean_name . '.php')) {
	         die($app_strings['ERROR_TYPE_NOT_VALID']);
	    }
	    require_once('modules/' . $module . '/' . $bean_name . '.php');
	    $focus = new $bean_name();
	    $focus->retrieve($_REQUEST['id']);
	    if(!$focus->ACLAccess('view')){
	        die($mod_strings['LBL_NO_ACCESS']);
	    } // if
    } // if
	$local_location = (isset($_REQUEST['isTempFile'])) ? "{$GLOBALS['sugar_config']['cache_dir']}/modules/Emails/{$_REQUEST['ieId']}/attachments/{$_REQUEST['id']}"
		 : $GLOBALS['sugar_config']['upload_dir']."/".$_REQUEST['id'];

	if(isset($_REQUEST['isTempFile']) && ($_REQUEST['type']=="SugarFieldImage")) {			
	    $local_location =  $GLOBALS['sugar_config']['upload_dir']."/".$_REQUEST['id'];	    
    }
    
	if(!file_exists( $local_location ) || strpos($local_location, "..")) {
		die($app_strings['ERR_INVALID_FILE_REFERENCE']);
	}
	else {
		$doQuery = true;

		if($file_type == 'documents') {
			// cn: bug 9674 document_revisions table has no 'name' column.
			$query = "SELECT filename name FROM document_revisions INNER JOIN documents ON documents.id = document_revisions.document_id ";
			//BEGIN SUGARCRM flav=pro ONLY
			if(!$focus->disable_row_level_security){
    			// We need to confirm that the user is a member of the team of the item.
                $focus->add_team_security_where_clause($query);
			}
            //END SUGARCRM flav=pro ONLY
			$query .= "WHERE document_revisions.id = '" . $_REQUEST['id'] ."'";
		} elseif($file_type == 'kbdocuments') {
				$query="SELECT document_revisions.filename name	FROM document_revisions INNER JOIN kbdocument_revisions ON document_revisions.id = kbdocument_revisions.document_revision_id INNER JOIN kbdocuments ON kbdocument_revisions.kbdocument_id = kbdocuments.id ";	 
            //BEGIN SUGARCRM flav=pro ONLY
            if(!$focus->disable_row_level_security){
                $focus->add_team_security_where_clause($query);
            }
            //END SUGARCRM flav=pro ONLY
			$query .= "WHERE document_revisions.id = '" . $_REQUEST['id'] ."'";
		}  elseif($file_type == 'notes') {
			$query = "SELECT filename name FROM notes ";
            //BEGIN SUGARCRM flav=pro ONLY
            if(!$focus->disable_row_level_security){
                $focus->add_team_security_where_clause($query);
            }
            //END SUGARCRM flav=pro ONLY
			$query .= "WHERE notes.id = '" . $_REQUEST['id'] ."'";
		} elseif( !isset($_REQUEST['isTempFile']) && !isset($_REQUEST['tempName'] ) && isset($_REQUEST['type']) && $file_type!='temp' ){ //make sure not email temp file.
			$query = "SELECT filename name FROM ". $file_type ." ";
            //BEGIN SUGARCRM flav=pro ONLY
            if(!$focus->disable_row_level_security){
                $focus->add_team_security_where_clause($query);
            }
            //END SUGARCRM flav=pro ONLY
			$query .= "WHERE ". $file_type .".id= '".$_REQUEST['id']."'";
		}elseif( $file_type == 'temp'){
			$doQuery = false;
		}

		if($doQuery && isset($query)) {
			$rs = $GLOBALS['db']->query($query);
			$row = $GLOBALS['db']->fetchByAssoc($rs);

			if(empty($row)){
				die($app_strings['ERROR_NO_RECORD']);
			}
			$name = $row['name'];
			$download_location = $GLOBALS['sugar_config']['upload_dir']."/".$_REQUEST['id'];
		} else if(isset(  $_REQUEST['tempName'] ) && isset($_REQUEST['isTempFile']) ){
			// downloading a temp file (email 2.0)
			$download_location = $local_location;
			$name = $_REQUEST['tempName'];
		}
		else if(isset($_REQUEST['isTempFile']) && ($_REQUEST['type']=="SugarFieldImage")) {
			$download_location = $local_location;
			$name = $_REQUEST['tempName'];
		}
		
		if(isset($_SERVER['HTTP_USER_AGENT']) && preg_match("/MSIE/", $_SERVER['HTTP_USER_AGENT']))
		{	
			$name = urlencode($name);
			$name = str_replace("+", "_", $name);
		}

		header("Pragma: public");
		header("Cache-Control: maxage=1, post-check=0, pre-check=0");
		if(isset($_REQUEST['isTempFile']) && ($_REQUEST['type']=="SugarFieldImage"))
			header("Content-type: image");
		else {
		    header("Content-type: application/force-download");
            header("Content-disposition: attachment; filename=\"".$name."\";");
		}
		header("Content-Length: " . filesize($local_location));
		header("Expires: 0");
		set_time_limit(0);

		@ob_end_clean();
		ob_start();

		//BEGIN SUGARCRM flav=int ONLY
		// awu: stripping out zend_send_file function call, the function changes the filename to be whatever is on the file system
		if(function_exists('zend_send_file')){
            zend_send_file($download_location);
		}else{
		//END SUGARCRM flav=int ONLY
	        echo file_get_contents($download_location);
	    //BEGIN SUGARCRM flav=int ONLY
		}
		//END SUGARCRM flav=int ONLY
		@ob_flush();
	}
}
?>
