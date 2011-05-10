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
 * $Id: view.edit.php 
 * Description: This file is used to override the default Meta-data EditView behavior
 * to provide customization specific to the Contacts module.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/MVC/View/views/view.detail.php');

class DCEInstancesViewDetail extends ViewDetail {
   
 	function DCEInstancesViewDetail(){
 		parent::ViewDetail();
 	}
 	
 	function preDisplay(){
        parent::preDisplay();
        if(!empty($this->bean->id)){
            //for the detail view of the licence duration field because 2 lists are linked to 1 field
            if($this->bean->type=='evaluation'){
                $GLOBALS['app_list_strings']['production_duration_list']=$GLOBALS['app_list_strings']['evaluation_duration_list'];
            }
            if(!isset($GLOBALS['app_list_strings']['production_duration_list'][$this->bean->license_duration])){
                if(is_int($this->bean->license_duration / 365)){
                    $GLOBALS['app_list_strings']['production_duration_list'][$this->bean->license_duration] = $this->bean->license_duration / 365 . " " . $GLOBALS['mod_strings']['LBL_YEARS'];
                }else{
                    $GLOBALS['app_list_strings']['production_duration_list'][$this->bean->license_duration] = $this->bean->license_duration . " " . $GLOBALS['mod_strings']['LBL_DAYS'];
                }
            }
            if(!$this->bean->license_key_status){
                $this->bean->license_key = $this->bean->license_key . "  <span class='error'>(" . $GLOBALS['mod_strings']['LBL_KEY_DISABLED'] . ")</span>";
            }
            echo "<input type='hidden' id='instance_status' name='instance_status' value='{$this->bean->status}'>";
            echo "<input type='hidden' id='instance_type' name='instance_type' value='{$this->bean->type}'>";
            echo "<input type='hidden' id='support_user_flag' name='support_user_flag' value='{$this->bean->support_user}'>";
        }
    }
}

?>