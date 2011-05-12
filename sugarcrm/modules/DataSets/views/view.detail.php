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
 * $Id: view.detail.php 
 * Description: This file is used to override the default Meta-data EditView behavior
 * to provide customization specific to the DataSets module.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('include/MVC/View/views/view.detail.php');

class DataSetsViewDetail extends ViewDetail {
   
 	function DataSetsViewDetail(){
 		parent::ViewDetail();
 	}
 	
 	/**
 	 * display
     *
     */
 	function display() {
 		parent::display();
 		
		global $current_user, $app_strings, $mod_strings;
		
		if(isset($this->bean->query_id) && !empty($this->bean->query_id)){
			//CHECK FOR SUB-QUERIES
			$this->bean->check_interlock();
			//OUTPUT THE DATASET
			$data_set = new CustomQuery();
			$data_set->retrieve($this->bean->query_id);
			$QueryView = new ReportListView();
			$QueryView->initNewXTemplate( 'modules/CustomQueries/QueryView.html',$mod_strings);
			$QueryView->setHeaderTitle($this->bean->name);
		
			//below: make sure to aquire the custom layout headers if available
			$QueryView->export_type = "Ent";
			
			$QueryView->xTemplateAssign('EDIT_INLINE', SugarThemeRegistry::current()->getImage('edit_inline','align="absmiddle" alt="'.$app_strings['LNK_EDIT'].'" border="0"'));
			$QueryView->xTemplateAssign('LEFTARROW_INLINE', SugarThemeRegistry::current()->getImage('calendar_previous','align="absmiddle" alt="'.$mod_strings['LBL_LEFT'].'" border="0"'));
			$QueryView->xTemplateAssign('RIGHTARROW_INLINE', SugarThemeRegistry::current()->getImage('calendar_next','align="absmiddle" alt="'.$mod_strings['LBL_RIGHT'].'" border="0"'));
			$QueryView->setup($data_set, $this->bean, "main", "CUSTOMQUERY");
			$query_results = $QueryView->processDataSet();
		
			if($query_results['result']=="Error"){
			
				if (is_admin($current_user)){	
					echo "<font color=\"red\"><b>".$query_results['result_msg']."".$app_strings['ERROR_EXAMINE_MSG']."</font><BR>".$query_results['msg']."</b>";	
				} else {
					echo "<font color=\"red\"><b>".$query_results['result_msg']."</font></b><BR>";	
				}	
		
				
			}
			
			//end if there is even a query for the data set
			} else {
				echo "<font color=\"red\"><b>".$app_strings['NO_QUERY_SELECTED']."</font></b><BR>";	
			}	
 	} //display
}

?>