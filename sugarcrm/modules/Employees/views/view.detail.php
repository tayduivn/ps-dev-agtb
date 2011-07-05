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
 * Description: This file is used to override the default Meta-data DetailView behavior
 * to provide customization specific to the Bugs module.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/MVC/View/views/view.detail.php');

class EmployeesViewDetail extends ViewDetail {

 	function EmployeesViewDetail(){
 		parent::ViewDetail();
 	}
 	
 	function display() {
 		//BEGIN SUGARCRM flav!=sales ONLY
       	if(is_admin($GLOBALS['current_user']) || $_REQUEST['record'] == $GLOBALS['current_user']->id) {
			 $this->ss->assign('DISPLAY_EDIT', true);
        }
        if(is_admin($GLOBALS['current_user'])){
 			$this->ss->assign('DISPLAY_DUPLICATE', true);
 		}
 		//END SUGARCRM flav!=sales ONLY
 		
 		$showDeleteButton = FALSE;
 		if(  $_REQUEST['record'] != $GLOBALS['current_user']->id && $GLOBALS['current_user']->isAdminForModule('Users') )
        {
            $showDeleteButton = TRUE;
 		     if( empty($this->bean->user_name) ) //Indicates just employee
 		         $deleteWarning = $GLOBALS['mod_strings']['LBL_DELETE_EMPLOYEE_CONFIRM'];
 		     else 
 		         $deleteWarning = $GLOBALS['mod_strings']['LBL_DELETE_USER_CONFIRM'];
 		     $this->ss->assign('DELETE_WARNING', $deleteWarning);
        }
        //BEGIN SUGARCRM flav=sales ONLY
        $showDeleteButton = FALSE;
        //END SUGARCRM flav=sales ONLY
        $this->ss->assign('DISPLAY_DELETE', $showDeleteButton);
        
 		parent::display();
 	}
}
?>
