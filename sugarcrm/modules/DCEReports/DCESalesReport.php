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
//BEGIN SUGARCRM flav=dce ONLY

function processReports(){
    
    
    
    global $mod_strings, $sugar_config;
    if(empty($mod_strings))$mod_strings = return_module_language('en_us', 'DCEReports');

    

        //grab each user that has an instance relationship entry
        $userQuery  = "Select distinct user_id, user_name from dceinstances_users udi ";
        $userQuery .= "left join users on users.id  = udi.user_id ";
        $userQuery .= "left join dceinstances inst on  inst.id = udi.instance_id ";
        $userQuery .= "where  inst.deleted = 0 and udi.deleted = 0 and users.deleted = 0 ";
    
        $db = DBManagerFactory::getInstance();
        $userRez = $db->query($userQuery);
    
        //grab all the users in an array
        while(($users[] = $db->fetchByAssoc($userRez)) !=null);
        
        //Foreach user in the array
        foreach($users as $user){
        if(empty($user))continue;            
        //grab each instance that has an instance relationship entry
        $instQuery  = " Select *, inst.id inst_id, license_expire_date from dceinstances inst ";
        $instQuery .= " left join dceinstances_users udi on  udi.instance_id = inst.id ";
        $instQuery .= " where  inst.deleted = 0 and udi.deleted = 0 and udi.user_id = '".$user['user_id']."' ";    
        $instRez = $db->query($instQuery);
    
        //grab all the instances assigned to this user
        while(($instArr[] = $db->fetchByAssoc($instRez)) !=null);
                //create smarty object
                $ss = new Sugar_Smarty();
                $ss->assign("MOD", $mod_strings);

                //populate the title variables
                $ss->assign("USRNAME", $user['user_name']);
                
                //get accounts not used in last 30 days
                $notUsed30 = whichInstances($instArr, 'notUsed30',$db);
                //populate list
                if(!empty($notUsed30))$ss->assign("notUsed30", $notUsed30);
        
                //get paid accounts expired this week
                $expiredPaid = whichInstances($instArr, 'expiredPaid',$db);
                //populate list
                if(!empty($expiredPaid))$ss->assign("expiredPaid", $expiredPaid);
        
                //get accounts expiring next 30 days
                $expired30 = whichInstances($instArr, 'expired30',$db);
                //populate list
                if(!empty($expired30))$ss->assign("expired30", $expired30);
                
                //get accounts expiring next 30-90 days
                $expired90 = whichInstances($instArr, 'expired90',$db);
                //populate list
                if(!empty($expired90))$ss->assign("expired90", $expired90);
        
                //get evals expired this week
                $expiredEvals = whichInstances($instArr, 'expiredEvals',$db);
                //populate list
                if(!empty($expiredEvals))$ss->assign("expiredEvals", $expiredEvals);
        
                //get evals expiring next week
                $expiredEvals7 = whichInstances($instArr, 'expiredEvals7',$db);
                //populate list
                if(!empty($expiredEvals7))$ss->assign("expiredEvals7", $expiredEvals7);

                //get HTML back
                $bodyHTML = $ss->fetch('modules/DCEReports/tpls/DCESalesEmail.tpl');
                
                //send out email  
                sendSalesReportsMessage($user['user_id'], $bodyHTML, $db);

        
        }
        

    
    
    }
    
    function whichInstances($instArr, $mode, $db){
        global $mod_strings;
        $returnArr = array();
        if(empty($instArr)) return '';
        
        if($mode == 'notUsed30'){
            foreach($instArr as $arr){
                if(empty($arr['inst_id'])  || empty($arr['license_expire_date']))continue;
                $templInfo = getVersionEdition($arr['dcetemplate_id'],$db);
                $arr['last_used'] = getLastAccess($arr['inst_id'], $db, $mod_strings);
                $arr['last_used'] = strtotime($arr['last_used']);
				$atim = TimeDate::getInstance()->fromString($arr['last_used'])->ts;
                 if(dateCheck($atim, $mode)){
                        $arr['expires'] = $arr['license_expire_date'];
                        $arr['version'] = $templInfo['sugar_version'].' '.$templInfo['sugar_edition'];
                        $arr['users'] =  getNumOfActiveUsers($arr['inst_id'], $db). $mod_strings['LBL_SALES_REPORT_OUT_OF'].$arr['licensed_users'];
                        $arr['account'] = getAccountName($arr['account_id'], $db, true);
                        $arr['last_used'] = getLastAccess($arr['inst_id'], $db, $mod_strings); 
                        $returnArr[] = $arr;
                }   
            }
        }else{
        
            if($mode == 'expiredPaid'){
                foreach($instArr as $arr){
                    if(empty($arr['inst_id'])  || empty($arr['license_expire_date']))continue;
                    $templInfo = getVersionEdition($arr['dcetemplate_id'],$db);
                    //compute the expiration date, based on license start date, and duration
                    $stim = strtotime($arr['license_expire_date']);
                    $atim = mktime((int)date("H",$stim),(int)date("i",$stim),(int)date("s",$stim),(int)date("m",$stim), (int)date("d",$stim), (int)date("Y",$stim));

                    if((isset($arr['type']) && $arr['type']!='evaluation') && dateCheck($atim, $mode)){
                        $arr['expires'] = $arr['license_expire_date'];
                        $arr['version'] = $templInfo ['sugar_version'].' '.$templInfo ['sugar_edition'];
                        $arr['users'] =  getNumOfActiveUsers($arr['inst_id'], $db). $mod_strings['LBL_SALES_REPORT_OUT_OF'].$arr['licensed_users'];
                        $arr['account'] = getAccountName($arr['account_id'], $db, true);
                        $arr['last_used'] = getLastAccess($arr['inst_id'], $db, $mod_strings); 
                        $returnArr[] = $arr;
                  }   
                }
            }else if($mode == 'expired30'){
                foreach($instArr as $arr){
                    if(empty($arr['inst_id'])  || empty($arr['license_expire_date']))continue;
                    $templInfo = getVersionEdition($arr['dcetemplate_id'],$db);
                    //compute the expiration date, based on license start date, and duration
                    $stim = strtotime($arr['license_expire_date']);
                    $atim = mktime((int)date("H",$stim),(int)date("i",$stim),(int)date("s",$stim),(int)date("m",$stim), (int)date("d",$stim), (int)date("Y",$stim));

                    if((isset($arr['type']) && $arr['type']!='evaluation') && dateCheck($atim, $mode)){
                        $arr['expires'] = $arr['license_expire_date'];  
                        $arr['version'] = $templInfo ['sugar_version'].' '.$templInfo ['sugar_edition'];
                        $arr['users'] =  getNumOfActiveUsers($arr['inst_id'], $db). $mod_strings['LBL_SALES_REPORT_OUT_OF'].$arr['licensed_users'];
                        $arr['account'] = getAccountName($arr['account_id'], $db, true);
                        $arr['last_used'] = getLastAccess($arr['inst_id'], $db, $mod_strings); 
                        $returnArr[] = $arr;
                    }
                }                   
            }else if($mode == 'expired90'){
                foreach($instArr as $arr){
                    if(empty($arr['inst_id'])  || empty($arr['license_expire_date']))continue;
                    $templInfo = getVersionEdition($arr['dcetemplate_id'],$db);
                    //compute the expiration date, based on license start date, and duration
                    $stim = strtotime($arr['license_expire_date']);
                    $atim = mktime((int)date("H",$stim),(int)date("i",$stim),(int)date("s",$stim),(int)date("m",$stim), (int)date("d",$stim), (int)date("Y",$stim));
                     
                    if((isset($arr['type']) && $arr['type']!='evaluation') && dateCheck($atim, $mode)){
                        $arr['expires'] = $arr['license_expire_date'];
                        $arr['version'] = $templInfo ['sugar_version'].' '.$templInfo ['sugar_edition'];
                        $arr['users'] =  getNumOfActiveUsers($arr['inst_id'], $db). $mod_strings['LBL_SALES_REPORT_OUT_OF'].$arr['licensed_users'];
                        $arr['account'] = getAccountName($arr['account_id'], $db, true);
                        $arr['last_used'] = getLastAccess($arr['inst_id'], $db, $mod_strings); 
                        $returnArr[] = $arr;
                  }   
                }                
            }else if($mode == 'expiredEvals'){
                foreach($instArr as $arr){
                    if(empty($arr['inst_id'])  || empty($arr['license_expire_date']))continue;
                    $templInfo = getVersionEdition($arr['dcetemplate_id'],$db);
                    //compute the expiration date, based on license start date, and duration
                    $stim = strtotime($arr['license_expire_date']);
                    $atim = mktime((int)date("H",$stim),(int)date("i",$stim),(int)date("s",$stim),(int)date("m",$stim), (int)date("d",$stim), (int)date("Y",$stim));
    
                    if((isset($arr['type']) && $arr['type']=='evaluation') && dateCheck($atim, $mode)){
                        $arr['expires'] = $arr['license_expire_date'];
                        $arr['version'] = $templInfo ['sugar_version'].' '.$templInfo ['sugar_edition'];
                        $arr['users'] = getNumOfActiveUsers($arr['inst_id'], $db).$mod_strings['LBL_SALES_REPORT_OUT_OF'].$arr['licensed_users'];
                        $arr['account'] = getAccountName($arr['account_id'], $db, true);
                        $arr['last_used'] = getLastAccess($arr['inst_id'], $db, $mod_strings); 
                        $returnArr[] = $arr;
                  }   
                }                
            }else if($mode == 'expiredEvals7'){
                foreach($instArr as $arr){
                    if(empty($arr['inst_id'])  || empty($arr['license_expire_date']))continue;
                    $templInfo = getVersionEdition($arr['dcetemplate_id'],$db);
                    //compute the expiration date, based on license start date, and duration
                    $stim = strtotime($arr['license_expire_date']);
                    $atim = mktime((int)date("H",$stim),(int)date("i",$stim),(int)date("s",$stim),(int)date("m",$stim), (int)date("d",$stim), (int)date("Y",$stim));    

                    if((isset($arr['type']) && $arr['type']=='evaluation') && dateCheck($atim, $mode)){
                        $arr['expires'] = $arr['license_expire_date'];
                        $arr['version'] = $templInfo ['sugar_version'].' '.$templInfo ['sugar_edition'];
                        $arr['users'] =  getNumOfActiveUsers($arr['inst_id'], $db).$mod_strings['LBL_SALES_REPORT_OUT_OF'].$arr['licensed_users'];
                        $arr['account'] = getAccountName($arr['account_id'], $db, true);
                        $arr['last_used'] = getLastAccess($arr['inst_id'], $db, $mod_strings); 
                        $returnArr[] = $arr;
                  }   
                }                
            }else{
                //no mode found, return empty array
                $returnArr = '';
                
            }
        }
        return $returnArr;
        
    }
    
    function dateCheck($ptim, $mode){
     
        if(empty($ptim))
        return false;
        
        //get current time
        $now = TimeDate::getInstance()->nowDb();
        $stim = strtotime($now);

        //compare the days
        if($mode == 'notUsed30'){
            //adjust timestamp
            $atim = mktime(date("H",$stim), date("i",$stim), date("s",$stim), date("m",$stim), date("d",$stim)-30,   date("Y",$stim));
            //check if passed in date is prior to last 30 days
            if($ptim <= $atim)  return true;
            
        }elseif($mode == 'expiredPaid'){
            //adjust timestamp
            $atim = mktime(date("H",$stim), date("i",$stim), date("s",$stim), date("m",$stim), date("d",$stim)-7,   date("Y",$stim));
            //check if passed in date is within last 7 days
            if(($atim <= $ptim) && ($ptim <= $stim) ) return true;
            
        }elseif($mode == 'expired30'){
            //adjust timestamp
            $atim = mktime(date("H",$stim), date("i",$stim), date("s",$stim), date("m",$stim), date("d",$stim)+30,   date("Y",$stim));
            //check if passed in date is within next 30 days
            if(($atim >= $ptim) && ($ptim > $stim) ) return true;
            
        }elseif($mode == 'expired90'){
            //adjust timestamp
            $atim = mktime(date("H",$stim), date("i",$stim), date("s",$stim), date("m",$stim), date("d",$stim)+31,   date("Y",$stim));
            $atim2 = mktime(date("H",$stim), date("i",$stim), date("s",$stim), date("m",$stim), date("d",$stim)+90,   date("Y",$stim));
            //check if passed in date is within next 90 days, but after 30 days
            if(($atim2 >= $ptim) && ($ptim >= $atim) ) return true;
            
        }elseif($mode == 'expiredEvals'){
            //adjust timestamp
            $atim = mktime(date("H",$stim), date("i",$stim), date("s",$stim), date("m",$stim), date("d",$stim)-7,   date("Y",$stim));
            //check if passed in date is within last 7 days
            if(($atim <= $ptim) && ($ptim < $stim) ) return true;            
        }elseif($mode == 'expiredEvals7'){
            //adjust timestamp
            $atim = mktime(date("H",$stim), date("i",$stim), date("s",$stim), date("m",$stim), date("d",$stim)+7,   date("Y",$stim));
            //check if passed in date is within next 7 days
            if(($atim >= $ptim) && ($ptim > $stim) ) return true;
        }

        return false;        
    }

   function sendSalesReportsMessage($userID, $reportBody, $db){
        global $sugar_config, $mod_strings, $timedate;
        
        
        //retrieve email address of user
        
        $q = "SELECT ea.email_address FROM email_addresses ea 
                LEFT JOIN email_addr_bean_rel ear ON ea.id = ear.email_address_id 
                WHERE ear.bean_module = 'Users'
                AND ear.bean_id = '{$userID}' 
                AND ear.deleted = 0
                ORDER BY ear.primary_address DESC";
        $r = $db->limitQuery($q, 0, 1);
        $emailAdd = $db->fetchByAssoc($r);

        //retrieve IT Admin Email
        $adm = new Administration();
        $adm->retrieveSettings();
        $itemail =$adm->settings['dce_primary_it_email'];  

        //get system default 'from' emails
        
        $emailObj = new Email();         
        $defaults = $emailObj->getSystemDefaultEmail();
        
        //create mailer object to send email
        require_once('include/SugarPHPMailer.php');   
            $mail = new SugarPHPMailer();
            $mail->setMailerForSystem();
            $mail->From = $defaults['email'];
            $mail->FromName = $defaults['name'];
			$mail->Encoding = 'base64';
            $mail->IsHTML(true);
            $mail->ClearAllRecipients();
            $mail->ClearReplyTos();
            $mail->Subject=$mod_strings['LBL_SALES_REPORT_SUBJ'];
            $mail->Body_html=from_html($reportBody);
            $mail->Body=from_html($reportBody);
            $mail->prepForOutbound();
            $mail->AddAddress($emailAdd['email_address']);
            $mail->AddBCC($itemail);   
            $success = $mail->Send();

                //now create email bean
                if($success){
                    
                    $emailObj = new Email();
                    $emailObj->team_id = 1;
                    $emailObj->to_addrs= '';
                    $emailObj->type= 'archived';
                    $emailObj->deleted = '0';
                    $emailObj->name = $mail->Subject ;
                    $emailObj->description = $mail->Body;
                    $emailObj->description_html =null;
                    $emailObj->from_addr = $mail->From;
                    $emailObj->parent_type = 'DCEReport';
                    $emailObj->date_sent =$timedate->now();
                    $emailObj->modified_user_id = '1';                               
                    $emailObj->created_by = '1';
                    $emailObj->status='sent';
                    $retId = $emailObj->save();
                    return $retId;
                }                                
                                
        
             return '';   
    }
    function getAccountName($account_id,$db,$linked = false){
        global $sugar_config;
        
           if(empty($sugar_config))
           $accountQry =  "Select name from accounts where id = '$account_id'";
           $accountRes = $db->limitQuery($accountQry, 0, 1);
           $account = $db->fetchByAssoc($accountRes);

                
            //create link tag if site url is specified
            if(!empty($sugar_config['site_url']) && $linked){
             $AHREF = '<a href=\''.$sugar_config['site_url'].'/index.php?module=Accounts&amp;action=DetailView&amp;record='.$account_id.'\'>';   
             $AHREF .= $account['name'];
             $AHREF .= '</a>';
             $account['name'] = $AHREF;
            }
            

           return $account['name'];
           
    }
    function getVersionEdition($template_id,$db){
           $tmplQry =  "Select sugar_version, sugar_edition from dcetemplates where id = '$template_id'";
           $tmplRes = $db->limitQuery($tmplQry, 0, 1);
           $template = $db->fetchByAssoc($tmplRes);
           
           return $template;
           
    }
    
    
    function  getLastAccess($id, $db, $mod_strings){
           $dceRprtQry =  "Select last_login_time from dcereports where instance_id = '$id' order by last_login_time desc";
           $dceRprtRes = $db->limitQuery($dceRprtQry, 0, 1);
           $dceRprt = $db->fetchByAssoc($dceRprtRes);
           if(empty($dceRprt['last_login_time'])){$dceRprt['last_login_time'] = $mod_strings['LBL_SALES_REPORT_NEVER'];}
           return $GLOBALS['timedate']->to_display_date_time($dceRprt['last_login_time']);
    }     
    
    
    function  getNumOfActiveUsers($id, $db){
            global $timedate;
            //get current time
            $now = TimeDate::getInstance()->nowDb();
            $stim = strtotime($now);
            $wtim = mktime(gmdate("H",$stim), gmdate("i",$stim), gmdate("s",$stim), gmdate("m",$stim), gmdate("d",$stim)-7,   gmdate("Y",$stim));
            //convert back into date format
            $sevenDaysAgo = $timedate->asDb($timedate->getNow()->get("-1 week"));

           $dceRprtQry  =  "Select max(num_of_users) num_of_users from dcereports where instance_id = '$id' ";
           $dceRprtQry .=  " and date_entered >= '$sevenDaysAgo' order by date_entered desc";
           $dceRprtRes = $db->limitQuery($dceRprtQry, 0, 1);
           $dceRprt = $db->fetchByAssoc($dceRprtRes);
           if(empty($dceRprt['num_of_users'])){$dceRprt['num_of_users'] = '0';}
echo"
total number of users for instance $id is ".$dceRprt['num_of_users']." 
";
                   return $dceRprt['num_of_users'];
        
    }         
    
//END SUGARCRM flav=dce ONLY
?>
