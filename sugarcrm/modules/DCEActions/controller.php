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

class DCEActionsController extends SugarController{
    function DCEActionsController(){
        parent::SugarController();
    }
    function action_downloadLogs(){
        $bean=new DCEAction();
        $bean->retrieve($_REQUEST['record']);
        $content=$bean->logs;
        $name=preg_replace("/[^a-zA-Z0-9s]/", "", $name);
        $name=str_replace(" ", "_", $bean->name);
        ob_clean();
        header("Pragma: cache");
        header("Content-type: application/octet-stream; charset=".$GLOBALS['locale']->getExportCharset());
        header("Content-Disposition: attachment; filename={$name}_Logs.log");
        header("Content-transfer-encoding: binary");
        header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT" );
        header("Cache-Control: post-check=0, pre-check=0", false );
        header("Content-Length: ".strlen($content));
        
        print $GLOBALS['locale']->translateCharset($content, 'UTF-8', $GLOBALS['locale']->getExportCharset());
        
        sugar_cleanup(true);
    }
    
   function action_RestartEmail(){
        if(isset($_REQUEST['record'])){
            require_once('modules/DCEActions/dceActionUtils.php');
            $bean=new DCEAction();
            $bean->disable_row_level_security = true;
            $bean->retrieve($_REQUEST['record']);

            //process request to resend email
            if(!empty($bean->type)){ 

                $inst = new DCEInstance();
                $inst->disable_row_level_security = true;
                $inst->retrieve($bean->instance_id) ;            
                $mode = '';
                $emailType = '';
                $parms = retrieveParamsFromString($bean->cleanup_parms);

                if($bean->status == 'failed' || $bean->status == 'suspended'){
                    $mode == 'failed';
                }elseif($bean->type == 'toggle_off'){
                    $mode == 'toggle';
                }elseif($bean->type == 'toggle_on'){
                    $mode == 'toggle';
                }elseif($bean->type == 'upgrade'){
                    $mode == 'upgrade';
                }else{
                 //all you need to get email addresses are instance and db, which have already been defined   
                }
                
                //now retrieve email type for email address retrieval
                if($bean->status == 'failed' || $bean->status == 'suspended'){
                    $emailType = 'failed';

                }elseif($bean->type == 'create' || $bean->type == 'clone' || $bean->type == 'recover'){
                    if($inst->type == 'evaluation'){
                        $emailType = 'eval';
                    }else{
                        $emailType = 'create';
                    }
                    
                }elseif($bean->type == 'toggle_on'){
                    $emailType = 'toggle';

                }elseif($bean->type == 'upgrade_test'){
                    $emailType = 'upgrade_test';
                    
                }elseif($bean->type == 'upgrade_live'){
                    $emailType = 'upgrade_live';
                    
                }elseif($bean->type == 'archive'){
                    $emailType = 'archive';
                    
                }else{
                 //the rest of actions do not send out email   
                }

                
                //retrieve email addresses
                $emails = returnEmailAdds($inst,$bean->db,$mode); 
                if(!empty($emails) && !empty($emails) && !empty($emailType)){    
                    $emailID = sendActionMessage($emails, $emailType, $act->instance_id, $parms);
                }  
            }
            header("Location: index.php?module=DCEActions&action=DetailView&record=".$_REQUEST['record']);
            
        
        }else{
            header("Location: index.php?module=DCEActions&action=index"); 
        }        
        
        sugar_cleanup(true);
    }    

    
   function action_RestartAction(){
        if(isset($_REQUEST['record'])){
            require_once('modules/DCEActions/dceActionUtils.php');            
            $bean=new DCEAction();
            $bean->retrieve($_REQUEST['record']);
            $bean->status='queued';
            $bean->save();
            header("Location: index.php?module=DCEActions&action=DetailView&record=".$_REQUEST['record']);
        
        }else{
            header("Location: index.php?module=DCEActions&action=index"); 
        }        
        
        sugar_cleanup(true);
    }    

}
?>