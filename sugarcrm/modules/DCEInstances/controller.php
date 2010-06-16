<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
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
/*
 * Created on Feb 20, 2008
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once('include/MVC/Controller/SugarController.php');


class DCEInstancesController extends SugarController{
    function DCEInstancesController(){
        parent::SugarController();
    }
    function action_save(){
        //Populate the URL field if impossible on editview with JS
        //One case is : create instance from cluster subpanel.
        if(empty($_REQUEST['record'])){
            if(!empty($_REQUEST['dcecluster_id'])){
                $clt= new DCECluster();
                $clt->retrieve($_REQUEST['dcecluster_id']);
                $cluster_url=$clt->url;
                $cluster_url_format=$clt->url_format;
                if($cluster_url_format == 'Instance_Name.URL'){
                    $cluster_url=preg_replace('/.*[wW]{3,3}\./','',$cluster_url);
                    $cluster_url=preg_replace('/.*[hH][tT]{2,2}[pP]/','',$cluster_url);
                    $cluster_url=str_replace("://","",$cluster_url);
                    $this->bean->url = "{$_REQUEST['name']}.$cluster_url";
                }else{
                    $this->bean->url = "$cluster_url/{$_REQUEST['name']}";
                    $this->bean->url=str_replace("//","/",$this->bean->url);
                    $this->bean->url=str_replace(":/","://",$this->bean->url);
                }
            }else{
                $this->action = 'editview';
                $this->errors[] = translate('ERR_CLUSTER_ID_MISSING', 'DCEInstances');
                $this->process();
                return false;
            }
        // Verify if no duplicate name
            $query = "SELECT name FROM dceinstances where deleted = 0 AND name='{$_REQUEST['name']}'";
            $rows = array();
            global $db;
            $result = $db->query($query);
            while (($row = $db->fetchByAssoc($result)) != null) {
               $rows[]=$row;
            }
            if(!empty($rows)){
                $this->action = 'editview';
                $this->errors[] = translate('ERR_DUPLICATE_NAME', 'DCEInstances');
                $this->process();
                return false;
            }
            //Instance name banned?
            require_once('modules/DCEInstances/banned.php');
            if(in_array($_REQUEST['name'],$banned)){
                $this->action = 'editview';
                $this->errors[] = translate('ERR_BANNED_NAME', 'DCEInstances');
                $this->process();
                return false;
            }
            if(!preg_match('/^\w*$/', $_REQUEST['name'])){
                $this->action = 'editview';
                $this->errors[] = translate('ERR_BANNED_CHARACTER', 'DCEInstances');
                $this->process();
                return false;
            }
            // if try to clone an instance clone the users and contacts relationships
            if(!empty($_REQUEST['parent_dceinstance_id'])){
                $parent = new DCEinstance();
                $parent->retrieve($_REQUEST['parent_dceinstance_id']);
                $this->bean->id=create_guid();
                $this->bean->new_with_id=true;
                clone_relationship($db, array($parent->field_name_map['contacts']['relationship'],$parent->field_name_map['users']['relationship']), 'instance_id', $parent->id, $this->bean->id);
            }
        }
        //set expiration date
        $lic_start = $this->bean->license_start_date;
        $lic_duration = $this->bean->license_duration;
        $this->bean->license_expire_date = $this->bean->returnExpirationDate($lic_start,$lic_duration);
        parent::action_save();
        
        //Fire "key" action if license key has changed.
        if(isset($_REQUEST['license_field_change']) && $_REQUEST['license_field_change']== true && $this->bean->status == 'live'){
            $this->bean->create_action($this->bean->id, 'key');
        }
    }
    function action_CreateAction()
    {        
        if((empty($_REQUEST['uid']) || empty($_REQUEST['record'])) && empty($_REQUEST['actionType'])){
            $header="Location: index.php?module=".$_REQUEST['module'];
            if(isset($_REQUEST['return_action'])){
                $header.="&action={$_REQUEST['return_action']}";
            }else{
                $header.="&action=ListView";
            }
            if(isset($_REQUEST['return_id'])){
                $header.="&record={$_REQUEST['return_id']}";
            }
            header($header);
            die();
        }
        $record =  '';
        $actionType = '';
        $startDate = ''; 
        $priority = '';
        $upgradeVars = array();
 
        if(isset($_REQUEST['record'])){$record = $_REQUEST['record'];}
        if(isset($_REQUEST['actionType'])){$actionType= $_REQUEST['actionType'];}
        if(isset($_REQUEST['startDate'])){$startDate = $_REQUEST['startDate'];} 
        if(isset($_REQUEST['priority'])){$priority = $_REQUEST['priority'];}
        
        //create instance to be used to call createAction method
        $inst = new DCEInstance();
        
        
        if(strpos($actionType,'clone')!==false){
            
            
        }elseif($actionType=='create'  && !empty($inst->dce_parent_id)){
          if(isset($_REQUEST['clonse_db']) and ($_REQUEST['clonse_db']!==false ||$_REQUEST['clonse_db']!=='false' )){
            $inst->create_action($record, $actionType, '', '', '', true);
          }  
               
            
        }elseif(strpos($actionType, 'upgrade')!==false){

        //if action type is from upgrade
        
            if(!empty($_REQUEST['uid'])){
                $uids = explode(",", $_REQUEST['uid']);
            }else{
                $uids[0]=$record;
            }

            if(isset($_REQUEST['delete_clone'])  && !empty($_REQUEST['delete_clone'])){
                    $upgradeVars['delete_clone'] = $_REQUEST['delete_clone'];
                }    
            
            if(empty($priority)){
                $priority= 1;
            }

                if(!empty($_REQUEST['totemplate'])){
                    $upgradeVars['totemplate']=$_REQUEST['totemplate'];
                }else{
                    echo translate('ERR_NO_TPL_TO_UPGRADE_TO','DCEInstances');
                    die();
                }

            foreach($uids as $record){
                $inst->create_action($record, $_REQUEST['actionType'], $startDate, $priority, $upgradeVars);
            }
        }else{
            $inst->create_action($record, $actionType, $startDate, $priority, '');
        }
        
        $urlSTR = 'index.php?module=DCEInstances';
        if(!empty($_REQUEST['return_id']))$urlSTR .='&record='.$_REQUEST['return_id'];
        if(!empty($_REQUEST['return_action']))$urlSTR .='&action='.$_REQUEST['return_action'];
        if(!empty($_REQUEST['totemplate']))$urlSTR .='&clear=1&query=1';
        header("Location: $urlSTR");
    }
    function action_DCEUpgradeStep2()
    {
        $this->view = 'dceupgradestep2';
    }
    function action_DCEUpgradeStep1()
    {
        $this->view = 'dceupgradestep1';
    }
    /**
     * Specify what happens after the deletion has occurred.
     */
    protected function pre_delete(){
        //create delete action, ONLY if status is not set to new 
        //if new, it means no instance has been deployed and we do not need to delete on dn
        if($this->bean->status != 'new'){            
            $inst = new DCEInstance();
            $inst->retrieve($this->bean->id);            
            $inst->create_action($_REQUEST['record'], 'delete', '', '', '');
        }
            
    }
    function action_License_Key(){
        global $timedate;
        $json = getJSONobj();

        $license_details = array('disable_license'=>false, 'enable_license'=>false, 'license_key'=>'', 'license_start_date'=>'', 'license_duration'=>'', 'license_expire_date'=>'', 'licensed_users'=>'', 'type'=>'', 'name'=>'', 'sugar_version'=>'', 'sugar_edition'=>'');

        //reset the license expiration date in case it was modified
        $lic_start = $timedate->to_db_date($_POST['license_start_date'], false);
        $lic_duration = $_POST['license_duration'];
        $inst = new DCEInstance;

        //set the new expiration date
        $_POST['license_expire_date'] = $inst->returnExpirationDate($lic_start, $lic_duration);


        //populate license details
        foreach($license_details as $k=>$v){
            if(isset($_POST[$k])){
                $license_details[$k]=$_POST[$k];
            }
        }

        if($_POST['license_action'] == 'disable'){
            if ($_POST['license_key_status'] == true){
                $license_details['disable_license']=true;
                $license_details['enable_license']=false;
            }else{ 
                $license_details['enable_license']=true;
                $license_details['disable_license']=false;
            }
        }
        
        $account_details = array('billing_address_street'=>'', 'billing_address_city'=>'', 'billing_address_state'=>'', 'billing_address_postalcode'=>'', 'billing_address_country'=>'');
        $contact_details = array('first_name'=>'', 'last_name'=>'', 'email1'=>'');
        if(isset($_POST['account_id'])){
            
            $account = new Account();
            $account->retrieve($_POST['account_id']);
            $account->load_relationship('contacts');
            $contacts = $account->contacts->get();
            if(isset($contacts[0])){
                $contact = new Contact();
                $contact->retrieve($contacts[0]);
                foreach($contact_details as $k=>$v){
                    $contact_details[$k]=$contact->$k;
                }
            }
            foreach($account_details as $k=>$v){
                if(isset($account->$k)){
                    $account_details[$k]=$account->$k;
                }
            }
            $account_details['account_name']=$account->name;
        }
        $user_details = array('user_name'=>'', 'first_name'=>'', 'last_name'=>'', 'email1'=>'');
        if(isset($_POST['current_user_id'])){
            
            $user = new User();
            $user->retrieve($_POST['current_user_id']);
            foreach($user_details as $k=>$v){
                if(isset($user->$k)){
                    $user_details[$k]=$user->$k;
                }
            }
        }
        $input_array = array('license_details'=>$license_details, 'account_details'=>$account_details, 'contact_details'=>$contact_details, 'user_details'=>$user_details);
        $result=$this->action_dce_get_key($input_array);
        if(!is_array($result) || !isset($result['error'])){
            $tmp=$result;
            if(is_array($result)){
                $tmp = implode(' - ', $result);
            }
            $result = array('error'=>array('number'=>100,'name'=>'Unknown Error','description'=>$tmp));
        }
        echo $json->encode($result);
        
    }
    function action_dce_get_key($input_array){
        require_once('include/nusoap/nusoap.php');
        require_once('soap/SoapError.php');
        require_once('soap/SoapHelperFunctions.php');
        $error = new SoapError();
        $soapclient = new nusoapclient('http://updates.sugarcrm.com/dce/soap.php');
        $err = $soapclient->getError();
        if ($err) {
            var_dump($err);
            die();
        }
        $input_array = array_get_name_value_lists($input_array);
        $try=0;
        $result = $this->get_license_from_server($soapclient, $input_array);

        if(isset($result['error'])){
            return $result;
        }else{
            $error->set_error('ERROR : action_dce_get_key()');
            return array('error'=>$error->get_soap_array());
        }
    }

private function get_license_from_server($soapclient, $input_array, $rec=false){

        $result = '';    
        //if authentication has not happened, authenticate
        if(!isset($_SESSION['dce_authentication_session_id']) || empty($_SESSION['dce_authentication_session_id']) || $rec){
               $result = $this->authenticate_license_session($soapclient);
        }

        //if authenticated, get the key    
        if(isset($_SESSION['dce_authentication_session_id']) && !empty($_SESSION['dce_authentication_session_id'])){

            $result = $soapclient->call('dce_get_key',array('session_id'=>$_SESSION['dce_authentication_session_id'], 'input_array'=>$input_array));
            
            if(isset($result['get_key_result']) && is_array($result['get_key_result'])){
                $result['get_key_result']=name_value_lists_get_array($result['get_key_result']);
            }
            
            //if session on license server expired, make recursive call toe repeat authentication and license retrieval
            //pass in true to recursive call so it runs only once and avoids an infinite loop
            if(isset($result['error']) && ($result['error']['number'] === '101' ||$result['error']['number'] === 101 )&& !$rec){
                $result =$this->get_license_from_server($soapclient, $input_array, true);
            }
            
            if(isset($result['error']) && $result['error']['number'] != 111){
                return $result;
            }
        }else{
            //not authenticated, return error message
            return $result;   
        }
    }

               


    private function authenticate_license_session($soapclient = ''){
        //recreate soap client if it was not passed in
            if(empty($soapclient)){
                $error = new SoapError();
                $soapclient = new nusoapclient('http://updates.sugarcrm.com/dce/soap.php');
                $err = $soapclient->getError();
                if ($err) {
                    var_dump($err);
                    die();
                }
            }
            
            //retrieve proper settings for auth array
             require('config.php');
            $focus = new Administration();
            $focus->retrieveSettings();
            if(!isset($focus->settings['dce_licensing_user']) || !isset($focus->settings['dce_licensing_password'])){
                $error->set_error('Sugar Licensing Password or Sugar Licensing User missing. Go in DCE Settings.');
                return array('error'=>$error->get_soap_array());
            }
            //create auth array
            $auth_array = array(
                            'application_id'=>'eaa40b743f9cbd1d4e87c1f7ef0b08dc',
                            'user_name'=>$focus->settings['dce_licensing_user'], 
                            'password'=>md5(blowfishDecode(blowfishGetKey('dce_licensing_password'), $focus->settings['dce_licensing_password']))
                        );
            $auth_array = array_get_name_value_lists($auth_array);

            //call soap authentication
            $result = $soapclient->call('dce_authentication',array('auth_array'=>$auth_array));

            //if successfully authenticated, return blank, otherwise return error
            if(is_array($result) && isset($result['id']) && $result['id'] != -1 && $result['error']['number'] == 0){
                $_SESSION['dce_authentication_session_id'] = $result['id'];
                return '';
            }else{
                return $result;
            }
    
    }

    /**
    *call by detailview.js
    *Verify that there is at least one user and one contact associate to this instance
    */
    function action_alert_on_deploy(){
        $alert_on_deploy=0;
        $query="SELECT id FROM dceinstances_users WHERE instance_id='{$_REQUEST['record']}' AND deleted=0";
        $rows = array();
        global $db;
        $result = $db->query($query);
        while (($row = $db->fetchByAssoc($result)) != null) {
           $rows[]=$row;
        }
        if(empty($rows)){
            $alert_on_deploy+=1;
        }
        $rows = array();
        $query="SELECT id FROM dceinstances_contacts WHERE instance_id='{$_REQUEST['record']}' AND deleted=0";
        $rows = array();
        $result = $db->query($query);
        while (($row = $db->fetchByAssoc($result)) != null) {
           $rows[]=$row;
        }
        if(empty($rows)){
            $alert_on_deploy+=2;
        }
        echo $alert_on_deploy;
    }
    /*
     * Call by editview.js
     * Calculate the expiration date for a specific start date and duration 
     */
    function action_returnExpirationDate(){
        if(!empty($_REQUEST['start_date']) && !empty($_REQUEST['duration'])){
            global $timedate;
            $lic_start = $timedate->to_db_date($_REQUEST['start_date'], false);
            $lic_duration = $_REQUEST['duration'];
            $inst = new DCEInstance;
            echo $inst->returnExpirationDate($lic_start, $lic_duration);
        }
    }
}
?>
