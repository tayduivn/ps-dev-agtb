<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/
 
 
 
      //create and execute query for queued actions    
    function cleanupActions($db=null){
        global $timedate;
        if(empty($db)) {
            $db = DBManagerFactory::getInstance();
        }

        $getActionQry = "select id, status, type from dceactions where status in ('done','failed') and deleted = 0 and priority <> '-1'";
        //grab first action

        $actRes = $db->limitQuery($getActionQry,0,1);
        $row=$db->fetchByAssoc($actRes);        
        echo"\n\n\n";
        print_r($row);
        echo"\n\n\n";
        //grab failures first and stop processing.
        if($row['status'] == 'failed'){
            cleanup_failures($row['id']);
            return;   
        }
        
        //check cleanup type
        switch($row['type']){
            case 'create':
             cleanup_create($row['id'],$db);
            break;
    
            case 'convert':
             //do nothing
             break;
    
            case 'clone':
             cleanup_clone($row['id'],$db);
             break;
    
            case 'archive':
             cleanup_archive($row['id'],$db);
             break;
    
            case 'delete':
             cleanup_delete($row['id'],$db);
             break;
    
            case 'recover':
             cleanup_recover($row['id'],$db);
             break;
    
            case 'toggle_on':
             cleanup_toggle($row['id'],$db);
             break;

            case 'toggle_off':
             cleanup_toggle($row['id'],$db);
             break;

            case 'upgrade_test':
             cleanup_upgrade($row['id'],$db,false);
             break;
    
            case 'upgrade_live':
             cleanup_upgrade($row['id'],$db,true);
             break;    
            default:
             break;
        }
      
        //check for stale actions to remove
        //clean_stale_actions($db);

        //get the time for 5 hours ago
        $now = $timedate->nowDb();
        $stim = strtotime($now);
        //remove a day from timestamp 
        $ytim = mktime(date("H",$stim)-5, date("i",$stim), date("s",$stim), date("m",$stim), date("d",$stim),   date("Y",$stim));
        //convert back into date format
        $staletime = $timedate->to_db($ytim);
               
        //grab all the actions of type started that are 5 hours old or more
        $getActionQry = "select id, status, type   from dceactions where status = 'started' and deleted = 0 and date_started < '$staletime'";
        $actRes = $db->limitQuery($getActionQry,0,1);

        //if result is not empty
        if(isset($actRes) && !empty($actRes)){
            //foreach action
            while(($row=$db->fetchByAssoc($actRes)) != null){        
                //set the status to fail
                clean_stale_actions($row['id']);
            }
        }
        
        
    }
 
    function cleanup_create($actionID,$db=null){
        global $timedate;
        
        
        $act = new DCEAction();
        $act->disable_row_level_security = true;        
        $act->retrieve($actionID) ;

        //rebuild cleanup parameters
        $parms = retrieveParamsFromString($act->cleanup_parms);

        
        //grab instance
        $inst = new DCEInstance();
        $inst->disable_row_level_security = true;
        $inst->retrieve($act->instance_id) ;

        //update Action Status
        $act->status='completed';
        $act->date_completed = $timedate->now();
        $act->save();

        //update Instance Status, and url
        $inst->status = 'live';
        if(isset($parms['site_url']) && !empty($parms['site_url'])){ 
            $inst->url = $parms['site_url'];
        }
        if(isset($parms['instance_path']) && !empty($parms['instance_path'])){ 
            $inst->instance_path = $parms['instance_path'];
        }
        if(isset($parms['site_pass']) && !empty($parms['site_pass'])){ 
            $inst->admin_pass = $parms['site_pass'];
            $inst->admin_user = 'admin';
        }
        if(isset($parms['db_user']) && !empty($parms['db_user'])){ 
            $inst->db_user = $parms['db_user'];
        }        
        $inst->save();
        $inst->save_relationship_changes(true);     

        //update Cluster url
        
        //grab instance
        $clst = new DCECluster();
        $clst->disable_row_level_security = true;
        $clst->retrieve($act->cluster_id) ;

        if(isset($parms['site_url']) && !empty($parms['site_url'])){
             
            $clst->url = dirname($parms['site_url']);
        }
        $clst->save();     

        

        //grab users email address to send message to
        $emails = returnEmailAdds($inst,$db); 

        //send message
//        if(!empty($emails)){
                       
          if($inst->type == 'evaluation'){
            $emailType = 'eval';
          }else{     
            $emailType = 'create';
          }
          //add license expiration date to parms for email.
          if(isset($inst->license_expire_date)){
            $parms['license_expire'] = $inst->license_expire_date; 
          }  
          $emailID = sendActionMessage($emails, $emailType, $inst->id, $parms);
    
            //save relationship of email with instance
            if (!empty($emailID)) {
                $inst->load_relationship('emails');
                $inst->emails->add($emailID);
    
            }
//        }
    }
 
     function cleanup_clone($actionID,$db=null){
        //same as cleanup_create()
        cleanup_create($actionID,$db);
    }

     function cleanup_archive($actionID,$db=null){
        global $timedate;
        
        
        $act = new DCEAction();
        $act->disable_row_level_security = true;        
        $act->retrieve($actionID) ;

        //rebuild cleanup parameters
        $parms = retrieveParamsFromString($act->cleanup_parms);

        
        //grab instance
        $inst = new DCEInstance();
        $inst->disable_row_level_security = true;
        $inst->retrieve($act->instance_id) ;

        //update Action Status
        $act->status='completed';
        $act->date_completed= $timedate->now();
        $act->save();


        //update Instance Status, and url
        $inst->status = 'archived';
        $inst->save();     
   

        //grab users email address to send message to
        $emails = returnEmailAdds($inst,$db); 

        //send message
        $emailType = 'archive';
        $emailID = sendActionMessage($emails, $emailType, $inst->id, $parms);
    
        //save relationship of email with instance
        if (!empty($emailID)) {
            $inst->load_relationship('emails');
            $inst->emails->add($emailID);

        }
    }
 
      function cleanup_delete($actionID,$db=null){
        global $timedate;
        
        $act = new DCEAction();
        $act->disable_row_level_security = true;        
        $act->retrieve($actionID) ;

        //update Action Status
        $act->status='completed';
        $act->date_completed = $timedate->now();
        $act->save();
        
        //now delete the instance bean
        
        //grab instance
        $inst = new DCEInstance();
        $inst->disable_row_level_security = true;
        $inst->retrieve($act->instance_id) ;
        $inst->mark_deleted($inst->id);
        
        

        }
    
    
      function cleanup_recover($actionID,$db=null){
        //same as cleanup_create()
        cleanup_create($actionID,$db);
           
    }


      function cleanup_toggle($actionID,$db=null){
        global $timedate;
        
        
        
        $act = new DCEAction();
        $act->disable_row_level_security = true;        
        $act->retrieve($actionID) ;


        //update Action Status
        $act->status='completed';
        $act->date_completed = $timedate->now();
        $act->save();

        //only send out email if toggle is for on, not off
        if($act->type=='toggle_on'){
            //rebuild cleanup parameters
            $parms = retrieveParamsFromString($act->cleanup_parms);
            
            $admin = new Administration();
            $admin->retrieveSettings();
            $parms['support_time_limit']=$admin->settings['dce_support_user_time_limit'];

            //grab users email address to send message to
            $emails = returnEmailAdds('',$db,'toggle',$act); 

            //send message
              $emailType = 'toggle';
                
            $emailID = sendActionMessage($emails, $emailType, $act->instance_id, $parms);

            //save relationship of email with instance
            if (!empty($emailID)) {
                $inst->load_relationship('emails');
                $inst->emails->add($emailID);
    
            }            
        }else{
            
            //grab instance
            $inst = new DCEInstance();
            $inst->disable_row_level_security = true;
            $inst->retrieve($act->instance_id) ;
    
            //set the isntance support_user flag back to 0
            $inst->support_user = 0;
            $inst->save();   
        }
    }



      function cleanup_upgrade($actionID,$db=null, $isLive){
        global $timedate;
        
        
        $act = new DCEAction();
        $act->disable_row_level_security = true;        
        $act->retrieve($actionID) ;


        //update Action Status
        $act->status='completed';
        $act->date_completed = $timedate->now();
        $act->save();

        //if is live then just send out email
        //rebuild cleanup parameters
        $parms = retrieveParamsFromString($act->cleanup_parms);

        //grab main it user email address to send message to
        $emails = returnEmailAdds('',$db,'upgrade'); 

        //link instance to new template
            //grab the action params which will have the new template id
            $act_parms = retrieveParamsFromString($act->action_parms);    
            
            //grab instance
            
            $inst = new DCEInstance();
            $inst->disable_row_level_security = true;
            $inst->retrieve($act->instance_id) ;
            //link the two
            $inst->dcetemplate_id = $act_parms['totemplate'];
            //set the status back to live and save changes
            $inst->status = 'live';    
            $inst->save();

        if($isLive){
            //set message type
            $emailType = 'upgrade_live';
            
            
        }else{
            //set message type
            $emailType = 'upgrade_test';

            //create clone if params were sent back and this is a test            
            if(isset($parms['clone'])){
                $newParmsARR = create_clone($act->instance_id, $parms);
                if(is_array($newParmsARR)){
                    foreach ($newParmsARR as $k=>$v){
                        $parms[$k] = $v;   
                    }                
                }                                
            }
    
        }
        //send out email message
        $emailID = sendActionMessage($emails, $emailType, $act->instance_id, $parms);
 
        //save relationship of email with instance
        if (!empty($emailID)) {
            $inst->load_relationship('emails');
            $inst->emails->add($emailID);

        }
    }


    function create_clone($parentID, $parms){
        $retARR = array();
        if(isset($parms['clone'])){
            
            //grab parent instance to make clone from
            
            $clnInst = new DCEInstance();
            $clnInst->disable_row_level_security = true;
            $clnInst->retrieve($parentID) ;


            if(strlen($clnInst->admin_pass)>6){
                $retARR['site_pass'] = 'same password as for '.$clnInst->name ;    
            }else{
                $retARR['site_pass'] = $clnInst->admin_pass;
            }
                        
            //set the parent id
            $clnInst->parent_dceinstance_id = $parentID;

            //clear the id and set license fields
            $clnInst->id = '';
            $clnInst->license_expire_date = '';
            $clnInst->date_entered = '';
            $clnInst->license_duration = '3';
            $clnInst->licensed_users = '3';
            $clnInst->license_key = 'clone_test';

            //populate with passed back values
            //split the clone params into elements
            $preCloneArr = explode('\| ', $parms['clone']);
            $cloneArray = array();
            //create clone array elements from clone param contents
            foreach($preCloneArr as $pcaV){
                $pcaV_split = explode('=', $pcaV);
                $cloneArray[$pcaV_split[0]] = $pcaV_split[1];
            }

            //populate instance values
            foreach($cloneArray as $col=>$val){
               if(empty($val) || empty($col) || !isset($clnInst->$col)) continue;
               $clnInst->$col = $val;
            }

            if(isset($parms['db_user'])) $cloneInst->db_user = $parms['db_user'];
            if(isset($parms['inst_name']))
            {
                $cloneInst->name = $parms['inst_name'];            
                $cloneInst->url = dirname($cloneInst->url).$parms['inst_name'];
                $cloneInst->instance_path = dirname($cloneInst->instance_path).$parms['inst_name'];

            }

            //save clone
            $clnInst->save();
        }

        return $retARR;
    }
    function cleanup_failures($actionID,$db=null){
        
        
        $act = new DCEAction();
        $act->disable_row_level_security = true;        
        $act->retrieve($actionID) ;
        $act->status='suspended' ;
        $act->save();


        //rebuild cleanup parameters
        $parms = retrieveParamsFromString($act->cleanup_parms);
        $parms['logs'] = $act->logs;

        
        //grab instance
        $inst = new DCEInstance();
        $inst->disable_row_level_security = true;
        $inst->retrieve($act->instance_id) ;

         //check cleanup type to update instance
        if($act->type == 'create'){
            $inst->status = 'new';
        }else{
            $inst->status = 'live';
        }
        $inst->save();

        //create clone if params were sent back and this is a test            
        if($act->type == 'upgrade_live' && isset($parms['clone'])){
            $newParmsARR = create_clone($act->instance_id, $parms);
            if(is_array($newParmsARR)){
                foreach ($newParmsARR as $k=>$v){
                    $parms[$k] = $v;   
                }
            }                
        }


        //grab users email address to send message to
        $emails = returnEmailAdds($inst,$db,'failed'); 

        //send message
        $emailType = 'failed';
            
        $emailID = sendActionMessage($emails, $emailType, $inst->id, $parms);
    
        //save relationship of email with instance
        if (!empty($emailID)) {
            $inst->load_relationship('emails');
            $inst->emails->add($emailID);

        }
    }


    function clean_stale_actions($row_id){
        
                //set the status to fail
                
                $act = new DCEAction();
                $act->disable_row_level_security = true;        
                $act->retrieve($row_id) ;
                $act->status = 'failed';
                $act->logs .= '----  action was started but never returned status report.  ----';  
                $act->save();
                // call failure cleanup        
                cleanup_failures($row_id,$db=null);         
    }


    function returnEmailAdds($inst,$db, $mode='', $act=''){
        $emailAdds = array();
        $beanIDs = '';

        //grab db instance if db is empty
        if(empty($db)) {
            $db = DBManagerFactory::getInstance();
        }


        if(($mode == 'failed') || ($mode == 'upgrade')){
            //do nothing, IT default email will get populated by default 

        }elseif($mode == 'toggle'){
            $beanIDs[] = $act->modified_user_id;
            
        }else{
            //grab ALL related users
            $inst->load_relationship('users');
            $relUsers = $inst->users->get();
            
            //we only want the contacts that are primary decision makers
            //construct query
            $primContQry = "select contact_id from dceinstances_contacts where instance_id='".$inst->id."' and contact_role = 'Primary Decision Maker' and deleted = 0 order by date_modified desc";

            //run query for contacts
            $primContRes = $db->query($primContQry);

            //use results to create list of primary contact ids's
            $primContacts = array();
            $primContactsRes = array();

            while(($primContactsRes[]=$db->fetchByAssoc($primContRes)) != null);

            foreach($primContactsRes as $contact_id){
                if(!empty($contact_id)  && isset($contact_id['contact_id']))
                $primContacts[] = $contact_id['contact_id'];
            }
            $beanIDs = array_merge($relUsers,$primContacts);
        }
        

        if(empty($beanIDs)){
            return '';
        }
        
        //retrieve the email of each user and contact
        $first = true;
        $emailStr = '';
        foreach($beanIDs as $id){
             //iterate thru ids and create string for query
             if($first){
                $emailStr .="'$id'";      
                $first = false;
             }else{
                $emailStr .=", '$id'";
             }
        }

        $emailAddStr ="";
        $first = true;

        //create and execute the query
        $email_query  = "select ea.email_address from email_addresses ea ";
        $email_query .= " left join email_addr_bean_rel eabr on ea.id = eabr.email_address_id ";
        $email_query .= " where ea.deleted = 0 and eabr.deleted = 0 and eabr.primary_address = 1 ";
        $email_query .= " and eabr.bean_id in ($emailStr )";

        $res = $db->query($email_query);
        //use results to create list of emails to return
        while(($emailAdds[]=$db->fetchByAssoc($res)) != null);

         foreach($emailAdds as $email){
             if($first){
                $emailAddStr .="$email";      
                $first = false;
             }else{
                $emailAddStr .=", $email";
             }
        }
            
        return $emailAdds;//$emailAddStr;                           
    }


    function returnTemplate($type){
        if (!isset($type) || empty($type)){
            return false; 
        }
        //grab the email template  value from Admin config settings
        $adm = new Administration();
        $adm->retrieveSettings();

        //make sure admin setting exists
        if (!isset($adm->settings['dce_'.$type.'_tmpl']) || empty($adm->settings['dce_'.$type.'_tmpl'])){
            return '';
        } 

        return $adm->settings['dce_'.$type.'_tmpl'];
    }
    
    function sendActionMessage($emails, $emailType,$inst_id, $parms){
        global $sugar_config;
        
        //retrieve the email template
        $emailTemp_id = returnTemplate($emailType);
        if(empty($emailTemp_id)) return false;
        
        
        $emailTemp = new EmailTemplate();
        $emailTemp->disable_row_level_security = true;
        $emailTemp->retrieve($emailTemp_id);
        
        //replace instance variables in email templates
        $htmlBody = $emailTemp->body_html;
        $body = $emailTemp->body;
        if(!isset($parms['site_url'])){ $parms['site_url'] = '';}
        if(!isset($parms['site_pass'])){ $parms['site_pass'] = '';}
        if(!isset($parms['license_expire'])){ $parms['license_expire'] = '';}
        if(!isset($parms['usr_pass'])){ $parms['usr_pass'] = '';}
        if(!isset($parms['usr_name'])){ $parms['usr_name'] = '';}
        if(!isset($parms['inst_name'])){ $parms['inst_name'] = '';}
        if(!isset($parms['parent_name'])){ $parms['parent_name'] = '';}
        if(!isset($parms['logs'])){ $parms['logs'] = '';}
        if(!isset($parms['support_time_limit'])){ $parms['support_time_limit'] = '';}
        
        
        $htmlBody = str_replace("#inst.url#", $parms['site_url'], $htmlBody);
        $htmlBody = str_replace("#inst.pass#", $parms['site_pass'], $htmlBody);
        $htmlBody = str_replace("#inst.expire#", $parms['license_expire'], $htmlBody);
        $htmlBody = str_replace("#inst.name#", $parms['inst_name'], $htmlBody);
        $htmlBody = str_replace("#usr.name#", $parms['usr_name'], $htmlBody);
        $htmlBody = str_replace("#usr.pass#", $parms['usr_pass'], $htmlBody);
        $htmlBody = str_replace("#parent.name#", $parms['parent_name'], $htmlBody);
        $htmlBody = str_replace("#inst.logs#", $parms['logs'], $htmlBody);
        $htmlBody = str_replace("#cfg.support_time_limit#", $parms['support_time_limit'], $htmlBody);

        $body = str_replace("#inst.url#", $parms['site_url'], $body);
        $body = str_replace("#inst.pass#", $parms['site_pass'], $body);
        $body = str_replace("#inst.expire#", $parms['license_expire'], $body);
        $body = str_replace("#inst.name#", $parms['inst_name'], $body);
        $body = str_replace("#usr.name#", $parms['usr_name'], $body);
        $body = str_replace("#usr.pass#", $parms['usr_pass'], $body);
        $body = str_replace("#parent.name#", $parms['parent_name'], $body);
        $body = str_replace("#inst.logs#", $parms['logs'], $body);
        $body = str_replace("#cfg.support_time_limit#", $parms['support_time_limit'], $body);

        $emailTemp->body_html = $htmlBody;
        $emailTemp->body = $body;
        require_once('include/SugarPHPMailer.php');
        

        //retrieve IT Admin Email
        $adm = new Administration();
        $adm->retrieveSettings();
        $itemail =$adm->settings['dce_primary_it_email'];            

        //retrieve email defaults
        $emailObj = new Email();         
        $defaults = $emailObj->getSystemDefaultEmail();
          
            $mail = new SugarPHPMailer();
            $mail->setMailerForSystem();
//            $mail->IsHTML(true);
            $mail->From = $defaults['email'];
            $mail->FromName = $defaults['name'];
            $mail->ClearAllRecipients();
            $mail->ClearReplyTos();
            $mail->Subject=from_html($emailTemp->subject);
            $mail->Body_html=from_html($emailTemp->body_html);
            $mail->Body=from_html($emailTemp->body);
            $mail->prepForOutbound();

            $hasRecipients = false;
            if(!empty($emails)){
                foreach($emails as $address){
                                print_r($emails);
                    if(empty($address['email_address'])) continue;
                    $mail->AddAddress($address['email_address']);
                    $hasRecipients = true;   
                }
            }
                if (!empty($itemail)){
                    if($hasRecipients){
                        $mail->AddBCC($itemail);
                    }else{
                        $mail->AddAddress($itemail);
                    }
                    $hasRecipients = true;
                }
                
                $success = false;
                if($hasRecipients){
                    $success = $mail->Send();
                }

                //now create email
                if($success){

                    $emailObj->team_id = 1;
                    $emailObj->to_addrs= '';
                    $emailObj->type= 'archived';
                    $emailObj->deleted = '0';
                    $emailObj->name = $mail->Subject ;
                    $emailObj->description = $mail->Body;
                    $emailObj->description_html =null;
                    $emailObj->from_addr = $mail->From;
                    $emailObj->parent_type = 'DCEInstance';
                    $emailObj->parent_id = $inst_id ;
                    $emailObj->date_sent =TimeDate::getInstance()->now();
                    $emailObj->modified_user_id = '1';                               
                    $emailObj->created_by = '1';
                    $emailObj->status='sent';
                    $retId = $emailObj->save();
                    return $retId;
                }                                
                                
        
             return '';   
    }
    
function retrieveParamsFromString($pStr=''){
    if(empty($pStr)){
        return $pStr;   
    }    
//$pStr='site_url:localhost,license_expire:12/12/2009,site_pass:fofoo,inst_name:myInst';
    $param_sects = explode(',', $pStr);
    $parms = array();
    foreach($param_sects as $ps){
        if(!empty($ps)){
            $pos = strpos($ps,':');
            if($pos !== false && $pos > 0){
                $k = substr($ps, 0, $pos);
                $v = substr($ps, $pos+1);
                $parms[trim($k)]= trim($v);   
            }
        }
    }
    return $parms;
}
?>
