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
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once('soap/SoapHelperFunctions.php');
require_once('soap/SoapTypes.php');
require_once('modules/Contacts/Contact.php');
require_once('modules/Accounts/Account.php');

require_once('soap/SoapPortalHelper.php');
require_once('config.php');
// BEGIN SUGARCRM flav=pro ONLY 
require_once('modules/Administration/SessionManager.php');
// END SUGARCRM flav=pro ONLY 
//require_once ('log4php/LoggerManager.php');
//$GLOBALS['log'] = LoggerManager::getLogger('SugarCRM');
if(!empty($GLOBALS['beanList']['KBDocuments'])) {
require_once('modules/KBDocuments/KBDocument.php');
}

/*************************************************************************************

THIS IS FOR PORTAL USERS


*************************************************************************************/
/*
this authenticates a user as a portal user and returns the session id or it returns false otherwise;
*/
$server->register(
        'portal_login',
        array('portal_auth'=>'tns:user_auth','user_name'=>'xsd:string', 'application_name'=>'xsd:string'),
        array('return'=>'tns:set_entry_result'),
        $NAMESPACE);

function portal_login($portal_auth, $user_name, $application_name){
	$error = new SoapError();
	$contact = new Contact();
	$result = login_user($portal_auth);
    
    if($result == 'fail' || $result == 'sessions_exceeded'){
        if($result == 'sessions_exceeded') {
            $error->set_error('sessions_exceeded');
        }
        else {
            $error->set_error('no_portal');
        }
        return array('id'=>-1, 'error'=>$error->get_soap_array());
    }
    global $current_user;
    //BEGIN SUGARCRM flav=pro ONLY 
    $sessionManager = new SessionManager();
    //END SUGARCRM flav=pro ONLY 
    
	if($user_name == 'lead'){
		session_start();
		$_SESSION['is_valid_session']= true;
		$_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
		$_SESSION['portal_id'] = $user->id;
		$_SESSION['type'] = 'lead';
        //BEGIN SUGARCRM flav=pro ONLY 
        $sessionManager->session_type = 'lead';
        $sessionManager->last_request_time = gmdate("Y-m-d H:i:s");
        $sessionManager->session_id = session_id();
        $sessionManager->save();
        //END SUGARCRM flav=pro ONLY 
		login_success();
		return array('id'=>session_id(), 'error'=>$error->get_soap_array());
	}else if($user_name == 'portal'){
		session_start();
		$_SESSION['is_valid_session']= true;
		$_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
		$_SESSION['portal_id'] = $user->id;
		$_SESSION['type'] = 'portal';
        //BEGIN SUGARCRM flav=pro ONLY 
        $sessionManager->session_type = 'portal';
        $sessionManager->last_request_time = gmdate("Y-m-d H:i:s");
        $sessionManager->session_id = session_id();
        $sessionManager->save();
        //END SUGARCRM flav=pro ONLY 
        $GLOBALS['log']->debug("Saving new session");
		login_success();
		return array('id'=>session_id(), 'error'=>$error->get_soap_array());
	}else{
	$contact = $contact->retrieve_by_string_fields(array('portal_name'=>$user_name, 'portal_active'=>'1', 'deleted'=>0) );
	if($contact != null){
		session_start();
		$_SESSION['is_valid_session']= true;
		$_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
		$_SESSION['user_id'] = $contact->id;
		$_SESSION['portal_id'] = $user->id;

		$_SESSION['type'] = 'contact';
		//BEGIN SUGARCRM flav=pro ONLY 
		$_SESSION['team_id'] = $contact->team_id;
		//END SUGARCRM flav=pro ONLY 
		$_SESSION['assigned_user_id'] = $contact->assigned_user_id;
        //BEGIN SUGARCRM flav=pro ONLY 
        $sessionManager->session_type = 'contact';
        $sessionManager->last_request_time = gmdate("Y-m-d H:i:s");
        $sessionManager->session_id = session_id();
        $sessionManager->save();
        //END SUGARCRM flav=pro ONLY 
		login_success();
		build_relationship_tree($contact);
		return array('id'=>session_id(), 'error'=>$error->get_soap_array());
	}
	}
	$error->set_error('invalid_login');
	return array('id'=>-1, 'error'=>$error->get_soap_array());
}

// BEGIN SUGARCRM flav=pro ONLY 
$server->register(
        'portal_login_contact',
        array('portal_auth'=>'tns:user_auth','contact_portal_auth'=>'tns:user_auth', 'application_name'=>'xsd:string'),
        array('return'=>'tns:set_entry_result'),
        $NAMESPACE);

function portal_login_contact($portal_auth, $contact_portal_auth, $application_name){
    $error = new SoapError();
    $contact = new Contact();
    $result = login_user($portal_auth);
    
    if($result == 'fail'){
    	$error->set_error('no_portal');
        
        return array('id'=>-1, 'error'=>$error->get_soap_array());
    }
    global $current_user;
    $sessionManager = new SessionManager();
    $contact = $contact->retrieve_by_string_fields(array('portal_name'=>$contact_portal_auth['user_name'], 'portal_password' => $contact_portal_auth['password'], 'portal_active'=>'1', 'deleted'=>0) );
    if($contact != null){
        session_start();
        $_SESSION['is_valid_session']= true;
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['user_id'] = $contact->id;
        $_SESSION['portal_id'] = $current_user->id;

        $_SESSION['type'] = 'contact';
        
        $_SESSION['team_id'] = $contact->team_id;
        
        $_SESSION['assigned_user_id'] = $contact->assigned_user_id;
        $sessionManager->session_type = 'contact';
        $sessionManager->last_request_time = gmdate("Y-m-d H:i:s");
        $sessionManager->session_id = session_id();
        $sessionManager->save();
        login_success();
        build_relationship_tree($contact);
        return array('id'=>session_id(), 'error'=>$error->get_soap_array());
    }
    else{
        $error->set_error('invalid_login');
        return array('id'=>-1, 'error'=>$error->get_soap_array());
    }
}
// END SUGARCRM flav=pro ONLY 
/*
this validates the session and starts the session;
*/
function portal_validate_authenticated($session_id){
//    $GLOBALS['log']->fatal('fds');
	$old_error_reporting = error_reporting(0);
	session_id($session_id);
	
	// This little construct checks to see if the session validated
	if(session_start()) {
        $valid_session = true;
        //BEGIN SUGARCRM flav=pro ONLY 
        $valid_session = SessionManager::getValidSession($session_id);
        //END SUGARCRM flav=pro ONLY 
		if(!empty($_SESSION['is_valid_session']) && $_SESSION['ip_address'] == $_SERVER['REMOTE_ADDR'] && $valid_session != null && ($_SESSION['type'] == 'contact' || $_SESSION['type'] == 'lead' || $_SESSION['type'] == 'portal')){
			global $current_user;
            $valid_session->last_request_time = gmdate("Y-m-d H:i:s");
            $valid_session->save();
			$current_user = new User();
			$current_user->retrieve($_SESSION['portal_id']);
			login_success();
			error_reporting($old_error_reporting);
			return true;
		}
	}
	session_destroy();
	error_reporting($old_error_reporting);
	return false;
}


$server->register(
        'portal_logout',
        array('session'=>'xsd:string'),
        array('return'=>'tns:error_value'),
        $NAMESPACE);
function portal_logout($session){
	$error = new SoapError();
	if(portal_validate_authenticated($session)){
//  $GLOBALS['log']->fatal('session destroy');
        //BEGIN SUGARCRM flav=pro ONLY 
        $sessionManager = new SessionManager();
        $sessionManager->archiveSession($session);
        //END SUGARCRM flav=pro ONLY 
		session_destroy();
		return $error->get_soap_array();
	}
	$error->set_error('invalid_session');
	return $error->get_soap_array();
}

$server->register(
        'portal_get_sugar_id',
        array('session'=>'xsd:string'),
        array('return'=>'tns:set_entry_result'),
        $NAMESPACE);
function portal_get_sugar_id($session){
	$error = new SoapError();
	if(portal_validate_authenticated($session)){
		return array('id'=>$_SESSION['portal_id'], 'error'=>$error->get_soap_array());
	}
	$error->set_error('invalid_session');
	return array('id'=>-1, 'error'=>$error->get_soap_array());

}

$server->register(
        'portal_get_sugar_contact_id',
        array('session'=>'xsd:string'),
        array('return'=>'tns:set_entry_result'),
        $NAMESPACE);
function portal_get_sugar_contact_id($session){
    $error = new SoapError();
    if(portal_validate_authenticated($session)){
        return array('id'=>$_SESSION['user_id'], 'error'=>$error->get_soap_array());
    }
    $error->set_error('invalid_session');
    return array('id'=>-1, 'error'=>$error->get_soap_array());

}


$server->register(
    'portal_get_entry_list',
    array('session'=>'xsd:string', 'module_name'=>'xsd:string','where'=>'xsd:string', 'order_by'=>'xsd:string', 'select_fields'=>'tns:select_fields'),
    array('return'=>'tns:get_entry_list_result'),
    $NAMESPACE);

function portal_get_entry_list($session, $module_name,$where, $order_by, $select_fields){
	return portal_get_entry_list_limited($session, $module_name, $where, $order_by, $select_fields, 0, "");
}

/*
 * Acts like a normal get_entry_list except it will build the where clause based on the name_value pairs passed
 * Here we assume 'AND'
 */
$server->register(
    'portal_get_entry_list_filter',
    array('session'=>'xsd:string', 'module_name'=>'xsd:string', 'order_by'=>'xsd:string', 'select_fields'=>'tns:select_fields', 'row_offset' => 'xsd:int', 'limit'=>'xsd:int', 'filter' =>'tns:name_value_operator_list'),
    array('return'=>'tns:get_entry_list_result'),
    $NAMESPACE);

function portal_get_entry_list_filter($session, $module_name, $order_by, $select_fields, $row_offset, $limit, $filter){
    global  $beanList, $beanFiles, $portal_modules;
    $error = new SoapError();
    if(! portal_validate_authenticated($session)){
        $error->set_error('invalid_session');
        return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
    }
    if($_SESSION['type'] == 'lead' || ($module_name == 'KBDocuments' && empty($GLOBALS['beanList']['KBDocuments']))){
        $error->set_error('no_access');
        return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
    }
    if(empty($beanList[$module_name])){
        $error->set_error('no_module');
        return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
    }
    
    //build the where clause
    
    $sugar = null;
    if($module_name == 'Cases'){
        $sugar = new aCase();
    }else if($module_name == 'Contacts'){
            $sugar = new Contact();
    }else if($module_name == 'Accounts'){
            $sugar = new Account();
    }else if($module_name == 'Bugs'){
        $sugar = new Bug();
    } else if($module_name == 'KBDocuments' || $module_name == 'FAQ') {
    	$sugar = new KBDocument();    
    } else {
        $error->set_error('no_module_support');
        return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
    }
    
    if($sugar != null){
        if(isset($filter) && is_array($filter)){
            $where = "";
            foreach($filter as $nvOp){
                $name = $nvOp['name'];
                $value = $nvOp['value'];
                $value_array = $nvOp['value_array'];
                $operator = $nvOp['operator'];
                //do nothing if all three values are not set
                if(isset($name) && (isset($value) || isset($value_array)) && isset($operator)){
                    if(!empty($where)){
                        $where .= ' AND ';   
                    }
                    if(isset($sugar->field_defs[$name])){
                        $where .=  "$sugar->table_name.$name $operator ";
                        if($sugar->field_defs['name']['type'] == 'datetime'){
                            $where .= db_convert("'$value'", 'datetime');
                        }else{
                            if(empty($value)) {
                                $tmp = array();
                                foreach($value_array as $v) {
                                    $tmp[] = PearDatabase::quote(from_html($v));
                                }
                                $where .= "('" . implode("', '", $tmp) . "')";                                
                            } else {
                                $where .= "'".PearDatabase::quote(from_html($value))."'";
                            }   
                        }   
                    }     
                }    
            }
        }      
        $GLOBALS['log']->debug("Portal where clause: ".$where);
        return portal_get_entry_list_limited($session, $module_name, $where, $order_by, $select_fields, $row_offset, $limit);          
    }else{
        $error->set_error('no_module_support');
        return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());  
    }
}


$server->register(
    'portal_get_entry',
    array('session'=>'xsd:string', 'module_name'=>'xsd:string', 'id'=>'xsd:string', 'select_fields'=>'tns:select_fields'),
    array('return'=>'tns:get_entry_result'),
    $NAMESPACE);

function portal_get_entry($session, $module_name, $id,$select_fields ){
	global  $beanList, $beanFiles;
	$error = new SoapError();
	if(! portal_validate_authenticated($session)){
		$error->set_error('invalid_session');
		return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
	}
	if($_SESSION['type'] == 'lead' || ($module_name == 'KBDocuments' && empty($GLOBALS['beanList']['KBDocuments']))){
		$error->set_error('no_access');
		return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
	}
	if(empty($beanList[$module_name])){
		$error->set_error('no_module');
		return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
	}

	if(empty($_SESSION['viewable'][$module_name][$id])){

		$error->set_error('no_access');
		return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
	}

	$class_name = $beanList[$module_name];
	require_once($beanFiles[$class_name]);
	$seed = new $class_name();
	//BEGIN SUGARCRM flav=pro ONLY 
	$seed->disable_row_level_security = true;
	//END SUGARCRM flav=pro ONLY 
	$seed->retrieve($id);
	if($module_name == 'KBDocuments') {
	   $body = $seed->get_kbdoc_body($id);
	   $seed->description = $body;	
	}

	$output_list = Array();


		//$loga->fatal("Adding another account to the list");
	$output_list[] = get_return_value($seed, $module_name);

	$output_list = filter_return_list($output_list, $select_fields, $module_name);
	$field_list = array();
	if(empty($field_list)){
			$field_list = get_field_list($seed);
	}
	$output_list = filter_return_list($output_list, $select_fields, $module_name);
	$field_list = filter_field_list($field_list,$select_fields, $module_name);

	return array('field_list'=>$field_list, 'entry_list'=>$output_list, 'error'=>$error->get_soap_array());
}

$server->register(
    'portal_set_entry',
    array('session'=>'xsd:string', 'module_name'=>'xsd:string',  'name_value_list'=>'tns:name_value_list'),
    array('return'=>'tns:set_entry_result'),
    $NAMESPACE);

function portal_set_entry($session,$module_name, $name_value_list){
	global  $beanList, $beanFiles, $valid_modules_for_contact;

	$error = new SoapError();
	if(!portal_validate_authenticated($session)){
		$error->set_error('invalid_session');
		return array('id'=>-1,  'error'=>$error->get_soap_array());
	}
	if(empty($beanList[$module_name])){
		$error->set_error('no_module');
		return array('id'=>-1, 'error'=>$error->get_soap_array());
	}
	if($_SESSION['type'] == 'lead' && $module_name != 'Leads'){
		$error->set_error('no_access');
		return array('id'=>-1, 'error'=>$error->get_soap_array());
	}

	if($_SESSION['type'] == 'contact' && !key_exists($module_name, $valid_modules_for_contact) ){
		$error->set_error('no_access');
		return array('id'=>-1, 'error'=>$error->get_soap_array());
	}


	$class_name = $beanList[$module_name];
	require_once($beanFiles[$class_name]);
	$seed = new $class_name();
	$is_update = false;
	$values_set = array();

	foreach($name_value_list as $value){
		if(isset($seed->$value['name']) && $seed->$value['name'] == 'id' && !empty($value['value'])){
			$is_update = true;
		}
		$values_set[$value['name']] = $value['value'];
		$seed->$value['name'] = $value['value'];
	}

	if(!isset($_SESSION['viewable'][$module_name])){
		$_SESSION['viewable'][$module_name] = array();
	}

	if(!$is_update){
	//BEGIN SUGARCRM flav=pro ONLY 
		if(!key_exists('team_id', $values_set) && isset($_SESSION['team_id'])){
			$seed->team_id = $_SESSION['team_id'];
		}
	//END SUGARCRM flav=pro ONLY 
		if(isset($_SESSION['assigned_user_id']) && (!key_exists('assigned_user_id', $values_set) || empty($values_set['assigned_user_id']))){
			$seed->assigned_user_id = $_SESSION['assigned_user_id'];
		}
		if(isset($_SESSION['account_id']) && (!key_exists('account_id', $values_set) || empty($values_set['account_id']))){
			$seed->account_id = $_SESSION['account_id'];
		}
		$seed->portal_flag = 1;
	}
	//BEGIN SUGARCRM flav=pro ONLY 
	$seed->disable_row_level_security = true;
	//END SUGARCRM flav=pro ONLY 
	$id = $seed->save();
	//BEGIN SUGARCRM flav=pro ONLY 
	$seed->disable_row_level_security = true;
	//END SUGARCRM flav=pro ONLY 
	set_module_in(array('in'=>"('$id')", 'list'=>array($id)), $module_name);
	if($_SESSION['type'] == 'contact' && $module_name != 'Contacts' && !$is_update){
		if($module_name == 'Notes'){
			$seed->contact_id = $_SESSION['user_id'];
			if(isset( $_SESSION['account_id'])){
				$seed->parent_type = 'Accounts';
				$seed->parent_id = $_SESSION['account_id'];

			}
			$id = $seed->save();
		}else{
			$seed->contact_id = $_SESSION['user_id'];

			if(isset( $_SESSION['account_id'])){
				$seed->account_id = $_SESSION['account_id'];

			}
			$seed->save_relationship_changes(false);
		}
	}
	return array('id'=>$id, 'error'=>$error->get_soap_array());

}



/*

NOTE SPECIFIC CODE
*/
$server->register(
        'portal_set_note_attachment',
        array('session'=>'xsd:string','note'=>'tns:note_attachment'),
        array('return'=>'tns:set_entry_result'),
        $NAMESPACE);

function portal_set_note_attachment($session,$note)
{
	$error = new SoapError();
	if(!portal_validate_authenticated($session)){
		$error->set_error('invalid_session');
		return array('id'=>'-1', 'error'=>$error->get_soap_array());
	}
	if($_SESSION['type'] == 'lead' || !isset($_SESSION['viewable']['Notes'][$note['id']])){
		$error->set_error('no_access');
		return array('id'=>-1, 'error'=>$error->get_soap_array());
	}
	require_once('modules/Notes/NoteSoap.php');
	$ns = new NoteSoap();
	$id = $ns->saveFile($note, true);
	return array('id'=>$id, 'error'=>$error->get_soap_array());

}

$server->register(
    'portal_remove_note_attachment',
    array('session'=>'xsd:string', 'id'=>'xsd:string'),
    array('return'=>'tns:error_value'),
    $NAMESPACE);

function portal_remove_note_attachment($session, $id)
{
    $error = new SoapError();
    if(! portal_validate_authenticated($session)){
        $error->set_error('invalid_session');
        return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
    }
    if($_SESSION['type'] == 'lead' || !isset($_SESSION['viewable']['Notes'][$id])){
        $error->set_error('no_access');
        return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
    }
    require_once('modules/Notes/Note.php');
    $focus = new Note();
    //BEGIN SUGARCRM flav=pro ONLY 
    $focus->disable_row_level_security = true;
    //END SUGARCRM flav=pro ONLY 
    $focus->retrieve($id);
    $result = $focus->deleteAttachment();

    return $error->get_soap_array();
}

$server->register(
    'portal_get_note_attachment',
    array('session'=>'xsd:string', 'id'=>'xsd:string'),
    array('return'=>'tns:return_note_attachment'),
    $NAMESPACE);

function portal_get_note_attachment($session,$id)
{

	$error = new SoapError();
	if(! portal_validate_authenticated($session)){
		$error->set_error('invalid_session');
		return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
	}
	if($_SESSION['type'] == 'lead' || !isset($_SESSION['viewable']['Notes'][$id])){
		$error->set_error('no_access');
		return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
	}
	$current_user = $seed_user;
	require_once('modules/Notes/Note.php');
	$note = new Note();
	//BEGIN SUGARCRM flav=pro ONLY 
	$note->disable_row_level_security = true;
	//END SUGARCRM flav=pro ONLY 
	$note->retrieve($id);
	require_once('modules/Notes/NoteSoap.php');
	$ns = new NoteSoap();
	if(!isset($note->filename)){
		$note->filename = '';
	}
	$file= $ns->retrieveFile($id,$note->filename);
	if($file == -1){
		$error->set_error('no_file');
		$file = '';
	}

	return array('note_attachment'=>array('id'=>$id, 'filename'=>$note->filename, 'file'=>$file), 'error'=>$error->get_soap_array());

}
$server->register(
    'portal_relate_note_to_module',
    array('session'=>'xsd:string', 'note_id'=>'xsd:string', 'module_name'=>'xsd:string', 'module_id'=>'xsd:string'),
    array('return'=>'tns:error_value'),
    $NAMESPACE);

function portal_relate_note_to_module($session,$note_id, $module_name, $module_id){
	global  $beanList, $beanFiles, $current_user;
	$error = new SoapError();
	if(! portal_validate_authenticated($session)){
		$error->set_error('invalid_session');
		return $error->get_soap_array();
		}
	if($_SESSION['type'] == 'lead' || !isset($_SESSION['viewable']['Notes'][$note_id]) || !isset($_SESSION['viewable'][$module_name][$module_id])){
		$error->set_error('no_access');
		return $error->get_soap_array();
		}
	if(empty($beanList[$module_name])){
		$error->set_error('no_module');
		return $error->get_soap_array();
	}

	$class_name = $beanList[$module_name];
	require_once($beanFiles[$class_name]);

	$seed = new $class_name();
	//BEGIN SUGARCRM flav=pro ONLY 
	$seed->disable_row_level_security = true;
	//END SUGARCRM flav=pro ONLY 
	$seed->retrieve($module_id);
	if($module_name == 'Cases' || $module_name == 'Bugs') {
		$seed->note_id =  $note_id;
		$seed->save(false);
	} else {
		$error->set_error('no_module_support');
		$error->description .= ': '. $module_name;
	}
	$GLOBALS['log']->setLevel(LOG4PHP_LEVEL_F_INT);
	return $error->get_soap_array();

}
$server->register(
    'portal_get_related_notes',
    array('session'=>'xsd:string', 'module_name'=>'xsd:string', 'module_id'=>'xsd:string',  'select_fields'=>'tns:select_fields', 'order_by'=>'xsd:string'),
    array('return'=>'tns:get_entry_result'),
    $NAMESPACE);

function portal_get_related_notes($session,$module_name, $module_id, $select_fields, $order_by){
	global  $beanList, $beanFiles;
	$error = new SoapError();
	if(! portal_validate_authenticated($session)){
		$error->set_error('invalid_session');
		return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
	}
	if($_SESSION['type'] == 'lead' ){
		$error->set_error('no_access');
		return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
	}
	if(empty($beanList[$module_name])){
		$error->set_error('no_module');
		return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
	}
	if(empty($_SESSION['viewable'][$module_name][$module_id])){
		$error->set_error('no_access');
		return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
	}

	if($module_name =='Contacts'){
		if($_SESSION['user_id'] != $module_id){
			$error->set_error('no_access');
			return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
		}
		$list = get_notes_in_contacts("('$module_id')", $order_by);
	}else{
		$list = get_notes_in_module("('$module_id')", $module_name, $order_by);
	}



	$output_list = Array();
	$field_list = Array();
	foreach($list as $value)
	{

		//$loga->fatal("Adding another account to the list");
		$output_list[] = get_return_value($value, 'Notes');
		$_SESSION['viewable']['Notes'][$value->id] = $value->id;
	if(empty($field_list)){
			$field_list = get_field_list($value);
		}
	}
	$output_list = filter_return_list($output_list, $select_fields, $module_name);
	$field_list = filter_field_list($field_list,$select_fields, $module_name);


	return array('result_count'=>sizeof($output_list), 'next_offset'=>0,'field_list'=>$field_list, 'entry_list'=>$output_list, 'error'=>$error->get_soap_array());
}

$server->register(
    'portal_get_related_list',
    array('session'=>'xsd:string', 'module_name'=>'xsd:string', 'rel_module'=>'xsd:string', 'module_id'=>'xsd:string',  'select_fields'=>'tns:select_fields', 'order_by'=>'xsd:string', 'offset' => 'xsd:int', 'limit' => 'xsd:int'),
    array('return'=>'tns:get_entry_result'),
    $NAMESPACE);

function portal_get_related_list($session, $module_name, $rel_module, $module_id, $select_fields, $order_by, $offset, $limit){
    global  $beanList, $beanFiles;
    $error = new SoapError();
    if(! portal_validate_authenticated($session)){
        $error->set_error('invalid_session');
        return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
    }
    if($_SESSION['type'] == 'lead' ){
        $error->set_error('no_access');
        return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
    }
    if(empty($beanList[$module_name])){
        $error->set_error('no_module');
        return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
    }
    if(empty($_SESSION['viewable'][$module_name][$module_id])){
        $error->set_error('no_access');
        return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
    }
   
    $list = get_related_in_module("('$module_id')", $module_name, $rel_module, $order_by, $offset, $limit);

    $output_list = Array();
    $field_list = Array();
    foreach($list as $value)
    {

        //$loga->fatal("Adding another account to the list");
        $output_list[] = get_return_value($value, $rel_module);
        $_SESSION['viewable'][$rel_module][$value->id] = $value->id;
    if(empty($field_list)){
            $field_list = get_field_list($value);
        }
    }
    $output_list = filter_return_list($output_list, $select_fields, $module_name);
    $field_list = filter_field_list($field_list,$select_fields, $module_name);


    return array('result_count'=>$list['result_count'], 'next_offset'=>0,'field_list'=>$field_list, 'entry_list'=>$output_list, 'error'=>$error->get_soap_array());
}

$server->register(
    'portal_get_module_fields',
    array('session'=>'xsd:string', 'module_name'=>'xsd:string'),
    array('return'=>'tns:module_fields'),
    $NAMESPACE);

function portal_get_module_fields($session, $module_name){
	global  $beanList, $beanFiles, $portal_modules, $valid_modules_for_contact;
	$error = new SoapError();
	$module_fields = array();
	if(! portal_validate_authenticated($session)){
		$error->set_error('invalid_session');
		$error->description .=$session;
		return array('module_name'=>$module_name, 'module_fields'=>$module_fields, 'error'=>$error->get_soap_array());
	}
	if($_SESSION['type'] == 'lead'  && $module_name != 'Leads'){
		$error->set_error('no_access');
		return array('module_name'=>$module_name, 'module_fields'=>$module_fields, 'error'=>$error->get_soap_array());
	}
	if(empty($beanList[$module_name])){
		$error->set_error('no_module');
		return array('module_name'=>$module_name, 'module_fields'=>$module_fields, 'error'=>$error->get_soap_array());
	}
	if(($_SESSION['type'] == 'portal'||$_SESSION['type'] == 'contact') &&  !key_exists($module_name, $valid_modules_for_contact)){
		$error->set_error('no_module');
		return array('module_name'=>$module_name, 'module_fields'=>$module_fields, 'error'=>$error->get_soap_array());
	}
	$class_name = $beanList[$module_name];
    require_once($beanFiles[$class_name]);
	$seed = new $class_name();
	$seed->fill_in_additional_detail_fields();
	return get_return_module_fields($seed, $module_name, $error->get_soap_array());
}

$server->register(
    'portal_get_subscription_lists',
    array('session'=>'xsd:string'),
    array('return'=>'tns:get_subscription_lists_result'),
    $NAMESPACE);

function portal_get_subscription_lists($session){
    global  $beanList, $beanFiles;

    $error = new SoapError();
    if(! portal_validate_authenticated($session)){
        $error->set_error('invalid_session');
        return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
    }
    
    require_once('modules/Campaigns/utils.php');
    
    $contact = new Contact();
    //BEGIN SUGARCRM flav=pro ONLY 
    $contact->disable_row_level_security = true;
    //END SUGARCRM flav=pro ONLY 
    $contact->retrieve($_SESSION['user_id']);

    if(!empty($contact->id)) {
        $result = get_subscription_lists_keyed($contact, true);       
    }
    
    
    $return_results = array('unsubscribed' => array(), 'subscribed' => array());
    
    foreach($result['unsubscribed'] as $newsletter_name => $data) {
        $return_results['unsubscribed'][] = array('name' => $newsletter_name, 'prospect_list_id' => $data['prospect_list_id'], 
                                                  'campaign_id' => $data['campaign_id'], 'description' => $data['description'],
                                                  'frequency' => $data['frequency']);
    }
    foreach($result['subscribed'] as $newsletter_name => $data) {
        $return_results['subscribed'][] = array('name' => $newsletter_name, 'prospect_list_id' => $data['prospect_list_id'], 
                                                'campaign_id' => $data['campaign_id'], 'description' => $data['description'],
                                                'frequency' => $data['frequency']);
    }

    return array('unsubscribed'=>$return_results['unsubscribed'], 'subscribed' => $return_results['subscribed'], 'error'=>$error->get_soap_array());
}

$server->register(
    'portal_set_newsletters',
    array('session'=>'xsd:string', 'subscribe_ids' => 'tns:select_fields', 'unsubscribe_ids' => 'tns:select_fields'),
    array('return'=>'tns:error_value'),
    $NAMESPACE);

function portal_set_newsletters($session, $subscribe_ids, $unsubscribe_ids){
    global  $beanList, $beanFiles;

    $error = new SoapError();
    if(! portal_validate_authenticated($session)){
        $error->set_error('invalid_session');
        return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
    }
    
    require_once('modules/Campaigns/utils.php');
    
    $contact = new Contact();
    //BEGIN SUGARCRM flav=pro ONLY 
    $contact->disable_row_level_security = true;
    //END SUGARCRM flav=pro ONLY 
    $contact->retrieve($_SESSION['user_id']);

    if(!empty($contact->id)) {
        foreach($subscribe_ids as $campaign_id) {
            subscribe($campaign_id, null, $contact, true);
        }
        foreach($unsubscribe_ids as $campaign_id) {
            unsubscribe($campaign_id, $contact);
        }
    }
    
    return $error->get_soap_array();
}

if (!empty($GLOBALS['beanList']['KBDocuments'])) {
$server->register(
    'portal_get_child_tags',
    array('session'=>'xsd:string', 'tag'=>'xsd:string'),
    array('return'=>'kbtag_list'),
    $NAMESPACE);
}
function portal_get_child_tags($session, $tag) {
    return portal_get_child_tags_query($session, $tag);
}

if (!empty($GLOBALS['beanList']['KBDocuments'])) {
$server->register(
    'portal_get_tag_docs',
    array('session'=>'xsd:string', 'tag'=>'xsd:string'),
    array('return'=>'kbtag_docs_list'),
    $NAMESPACE);
}
function portal_get_tag_docs($session, $tag) {
    return portal_get_tag_docs_query($session, $tag);
}

if (!empty($GLOBALS['beanList']['KBDocuments'])) {
$server->register(
    'portal_get_kbdocument_attachment',
    array('session'=>'xsd:string', 'id'=>'xsd:string'),
    array('return'=>'tns:return_note_attachment'),
    $NAMESPACE);
}
function portal_get_kbdocument_attachment($session, $id)
{
	$error = new SoapError();
	if(!portal_validate_authenticated($session)){
	   $error->set_error('invalid_session');
	   return array('result_count'=>-1, 'entry_list'=>array(), 'error'=>$error->get_soap_array());
	}
	require_once('modules/KBDocuments/KBDocumentSoap.php');
	$ns = new KBDocumentSoap($id);
	$file= $ns->retrieveFile($id);
	if($file == -1){
	   $error->set_error('no_file');
	   $file = '';
	}
	return array('note_attachment'=>array('id'=>$id, 'filename'=>$ns->retrieveFileName($id), 'file'=>$file), 'error'=>$error->get_soap_array());
}

if (!empty($GLOBALS['beanList']['KBDocuments'])) {
$server->register(
    'portal_get_kbdocument_body',
    array('session'=>'xsd:string', 'id'=>'xsd:string'),
    array('return'=>'xsd:string'),
    $NAMESPACE);
}
function portal_get_kbdocument_body($session, $id) {
    return portal_get_kbdocument_body_query($session, $id);
}
?>