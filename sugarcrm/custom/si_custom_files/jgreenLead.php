<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-professional-eula.html
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
/*********************************************************************************
 * $Id$
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
 
 require_once('modules/Leads/Lead.php');
 require_once('modules/Contacts/Contact.php');
require_once('modules/Accounts/Account.php');
 
 class jgreenLead extends Lead {
 	
 	var $potential_parent_array = array();
 	var $potential_parent_contact_array = array();
 	var $potential_parent_account_array = array();
 	var $potential_contacts_found = false;
 	var $potential_leads_found = false;
 	var $potential_accounts_found = false;
 	
 	function jgreenLead(){
 		parent::Lead();	
 	}

 	
 	function create_list_query($order_by, $where, $show_deleted=0)
	{
		$custom_join = $this->custom_fields->getJOIN();
		$query = "SELECT ";
		$query .= "$this->table_name.*, users.user_name assigned_user_name";
		$query .= ", teams.name team_name";
		if($custom_join){
   			$query .= $custom_join['select'];
 		}
			$query .= " FROM leads ";

		// We need to confirm that the user is a member of the team of the item.
		$this->add_team_security_where_clause($query);
			
		$query .= " LEFT JOIN users ON leads.assigned_user_id=users.id ";

		$query .= " LEFT JOIN teams ON leads.team_id=teams.id ";

		if($custom_join){
			$query .= $custom_join['join'];
		}
		
		//JGREEN SUGAR INTERNAL customization to handle showing only parents on related lead popup		
		$where_auto = '1=1';
		if($show_deleted == 0){
			$where_auto = " leads.deleted=0 AND leads_cstm.lead_relation_c != 'Child' ";
		}	
		//JGREEN SUGAR INTERNAL customization to handle not showing self on related lead popup
		if(!empty($_REQUEST['special_parent_id'])){
			$where_auto .= " AND leads.id != '".$_REQUEST['special_parent_id']."' ";
		}				
		//end sugar internal customizations	-jgreen

		if($where != ""){
			$searchArray = array(
				0 => '/leads.last_name like \'%(.*)%\'/',
				1 => '/leads.first_name like \'%(.*)%\'/',
				2 => '/leads.account_name like \'%(.*)%\'/',
			);
			$replaceArray = array(
				0 => 'leads.last_name like \'\1%\'',
				1 => 'leads.first_name like \'\1%\'',
				2 => 'leads.account_name like \'\1%\'',
			);
			
			$new_where = preg_replace($searchArray, $replaceArray, $where);
			$query .= "where $new_where AND ".$where_auto;
		}
		else
			$query .= "where ".$where_auto; //."and (leads.converted='0')";

		if(!empty($order_by))
			$query .= " ORDER BY $order_by";

		return $query;
	}
} //end class jgreenLead
 	
 
 
 
 
 
 
 
 
 ?>
