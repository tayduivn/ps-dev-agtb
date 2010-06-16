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


        if(isset($_REQUEST['record']) && !empty($_REQUEST['record'])){ 
            //retieve Instance
            
            $inst = new DCEInstance();
            $inst->retrieve($_REQUEST['record']);

            //change status of instance to show an action is in progress
            $inst->status = 'live';
            $inst->type = 'production';
            $inst->save();
    
            //create dce action for tracking purposes
            
            $action = new DCEAction();
            $action->name = $inst->name.' create action';
            $action->instance_id = $inst->id;
            $action->cluster_id = $inst->dcecluster_id;
            $action->template_id = $inst->dcetemplate_id;
            $action->type = 'convert';
            $action->status = 'deleted';
            $action->save();
        }
        $urlSTR = 'index.php?module=DCEInstances';
        if(isset($_REQUEST['return_id']))$urlSTR .='&record='.$_REQUEST['return_id'];
        if(isset($_REQUEST['return_action']))$urlSTR .='&action='.$_REQUEST['return_action'];
        
        header("Location: $urlSTR");
        //redirect back

?>
