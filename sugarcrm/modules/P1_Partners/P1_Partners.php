<?PHP
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/**
 * THIS CLASS IS FOR DEVELOPERS TO MAKE CUSTOMIZATIONS IN
 */

require_once('modules/Opportunities/Opportunity.php');

class P1_Partners extends Opportunity {

	var $object_name = 'P1_Partners';	
	var $module_dir = 'P1_Partners';
	
	function P1_Partners(){			
		parent::Opportunity();
	}

	/**
	 * Overriding base SugarBean function so that we do not need to overload the create_new_list_query to get the custom fields join.
	 * The custom field join checks to see if this module has custom fields but since we are using opportunities for the model and 
	 * not this class, we return true. Otherwise we need to add the join in the create_new_list_query method.
	 *
	 * @return unknown
	 */
	function hasCustomFields(){
    		return TRUE;
  	}
	/**
	 * Overriding base SugarBean function and passing in Opportunities module as otherwise the custom fields are not retrieved and 
	 * populated correctly.
	 *
	 */
  	  function setupCustomFields($module_name, $clean_load=true)
   	 {
		parent::setupCustomFields('Opportunities', $clean_load);
  	  }
  	  
	public function create_new_list_query($order_by, $where,$filter=array(),$params=array(), $show_deleted = 0,$join_type='', $return_array = false,$parentbean, $singleSelect = false){
	  /*
	  ** @author: dtam
	  ** SUGARINTERNAL CUSTOMIZATION
	  ** ITRequest #: 18979, 19916
	  ** Description: allow Emea teams a and b to filter by other team members and view team members opps, also give access for Sales Manager oppQ to see
	  **              inside sales reps and channel managers opportunities
	  */
	  global $current_user;
	  $RoleNames=array('EMEA AB','EMEA A','EMEA B','Inside Sales Rep','Sales Manager OppQ','US Sales Northeast','US Sales Southeast','US Sales Central','US Sales West');
	  $roleName2Guid = array();
	  $db = DBManagerFactory::getInstance();
	  foreach ($RoleNames as $name) {
	    $query= "select id from acl_roles where name ='" .  $name ."'";
	    $result = $db->query($query, true,"Error ");
	    $row = $db->fetchByAssoc($result);
	    $roleName2Guid[$name]=$row['id'];
	  }
	  foreach ($roleName2Guid as $GUID) { // loop over each role guid
	    $roleQuery = "SELECT user_id as id FROM acl_roles_users WHERE role_id='" . $GUID . "' AND deleted=0";
	    $RoleMembers = $this->build_related_list($roleQuery, new User()); // get members
	    foreach ($RoleMembers as $user) {
	      $RoleMemberGUIDS[$GUID][] = $user->id; // create arrays of user guids per role
	    }
	  }
	  // build emea AB user guid array
	  $emeaABUserIds = array_merge($RoleMemberGUIDS[$roleName2Guid['EMEA A']],$RoleMemberGUIDS[$roleName2Guid['EMEA B']]);
	  //build array of user guids for inside sales reps and channel managers
	  $ISRepsChannelManagerIds=array_merge($RoleMemberGUIDS[$roleName2Guid['Inside Sales Rep']],$RoleMemberGUIDS[$roleName2Guid['Sales Manager OppQ']]);

	  if($where == "(opportunities_cstm.accepted_by_partner_c = 'R')" && isset($_REQUEST['open_tasks'])) {
	    $where = "(opportunities_cstm.accepted_by_partner_c = 'R') OR (SELECT count(*) FROM tasks WHERE parent_id = opportunities.id AND status = 'Not Started' and deleted = 0) > 0";
	  }

	  $query_array = parent::create_new_list_query($order_by, $where, $filter, $params, $show_deleted, $join_type, $return_array, $parentbean, $singleSelect);
	  //Commenting this for testing purposes. Actual query must always filter all opportunities assigned to the logged in user and where sales stage is not 98% or 0%
	  $query_array['select'] .= ",accounts.billing_address_city account_billing_city, accounts.billing_address_state account_billing_state, accounts.billing_address_country account_billing_country";
	  
	  if ($current_user->check_role_membership('EMEA AB')) {  //role EMEA_AB has access to all emea_b and emea_a opps
	    $emea_users = implode("','", $emeaABUserIds);
	    $query_array['where'] .= " and opportunities.assigned_user_id IN ('{$emea_users}') and opportunities.sales_stage NOT IN ('Finance Closed', 'Closed Lost', 'Closed Won') ";
	  } elseif ($current_user->check_role_membership('EMEA A')) { //role EMEA_A has access to all emea_a opps
	    $emea_a_users = implode("','", $RoleMemberGUIDS[$roleName2Guid['EMEA A']]);
	    $query_array['where'] .= " and opportunities.assigned_user_id IN ('{$emea_a_users}') and opportunities.sales_stage NOT IN ('Finance Closed', 'Closed Lost', 'Closed Won') ";	
	  } elseif ($current_user->check_role_membership('EMEA B')) { //role EMEA_B has access to all emea_b opps
	    $emea_b_users = implode("','", $RoleMemberGUIDS[$roleName2Guid['EMEA B']]);
	    $query_array['where'] .= " and opportunities.assigned_user_id IN ('{$emea_b_users}') and opportunities.sales_stage NOT IN ('Finance Closed', 'Closed Lost', 'Closed Won') ";
	  } elseif ($current_user->check_role_membership('US Sales Northeast')) { //role us sales northeast has access to all emea_b opps
	    $us_ne_users = implode("','", $RoleMemberGUIDS[$roleName2Guid['US Sales Northeast']]);
	    $query_array['where'] .= " and opportunities.assigned_user_id IN ('{$us_ne_users}') and opportunities.sales_stage NOT IN ('Finance Closed', 'Closed Lost', 'Closed Won') ";
	  } elseif ($current_user->check_role_membership('US Sales Southeast')) { //role us sales southeast has access to all emea_b opps
	    $us_se_users = implode("','", $RoleMemberGUIDS[$roleName2Guid['US Sales Southeast']]);
	    $query_array['where'] .= " and opportunities.assigned_user_id IN ('{$us_se_users}') and opportunities.sales_stage NOT IN ('Finance Closed', 'Closed Lost', 'Closed Won') ";
	  } elseif ($current_user->check_role_membership('US Sales West')) { //role us sales west has access to all emea_b opps
	    $us_w_users = implode("','", $RoleMemberGUIDS[$roleName2Guid['US Sales West']]);
	    $query_array['where'] .= " and opportunities.assigned_user_id IN ('{$us_w_users}') and opportunities.sales_stage NOT IN ('Finance Closed', 'Closed Lost', 'Closed Won') ";
	  } elseif ($current_user->check_role_membership('US Sales Central')) { //role us sales central has access to all emea_b opps
	    $us_c_users = implode("','", $RoleMemberGUIDS[$roleName2Guid['US Sales Central']]);
	    $query_array['where'] .= " and opportunities.assigned_user_id IN ('{$us_c_users}') and opportunities.sales_stage NOT IN ('Finance Closed', 'Closed Lost', 'Closed Won') ";
	  } elseif ($current_user->check_role_membership('Sales Manager OppQ')) { //role Sales Mananger OppQ has access to filter on inside sales and channnel managers
	    $ISrepsChannelManagers_users = implode("','", $ISRepsChannelManagerIds);
	    $query_array['where'] .= " and opportunities.assigned_user_id IN ('{$ISrepsChannelManagers_users}') and opportunities.sales_stage NOT IN ('Finance Closed', 'Closed Lost', 'Closed Won') ";
	  }
	  // end fix
	  else {
	    /* END SUGARINTERNAL CUSTOMIZATION */
	    if (isset($filter['assigned_user_id'])) {
	      echo "<br><span style='color: red;'>There are no records matching your search parameters because you are trying to filter on assigned user and only members of EMEA roles have access to this functionality. Please clear your search and try again. </span><br>";
	    }
	    $query_array['where'] .= " and opportunities.assigned_user_id = '{$GLOBALS['current_user']->id}' and opportunities.sales_stage NOT IN ('Finance Closed', 'Closed Lost', 'Closed Won') ";
	  }

		//$query_array['where'] .= " and opportunities.sales_stage NOT IN ('Finance Closed', 'Closed Lost', 'Closed Won') ";
		//IT Request #10726 - When trying to search for opps not assigned to partners, system returns all results.  SearchForm removes all requests that contain empty strings
		//so this is the only way to add these filters.
		if( isset($_REQUEST['partner_assigned_to_c_advanced']) && (count($_REQUEST['partner_assigned_to_c_advanced']) == 1)  && ($_REQUEST['partner_assigned_to_c_advanced'][0] == '') )
			$query_array['where'] .= " and (opportunities_cstm.partner_assigned_to_c = '' OR opportunities_cstm.partner_assigned_to_c is null) ";

		//IT Request #10726 - When trying to search for opps not assigned to partners, system returns all results.  SearchForm removes all requests that contain empty strings
		//so this is the only way to add these filters.
		if( isset($_REQUEST['partner_assigned_to_c_advanced']) && (count($_REQUEST['partner_assigned_to_c_advanced']) == 1)  && ($_REQUEST['partner_assigned_to_c_advanced'][0] == '') )
			$query_array['where'] .= " and (opportunities_cstm.partner_assigned_to_c = '' OR opportunities_cstm.partner_assigned_to_c is null) ";

		if( isset($_REQUEST['accepted_by_partner_c_advanced']) && (count($_REQUEST['accepted_by_partner_c_advanced']) == 1)  && ($_REQUEST['accepted_by_partner_c_advanced'][0] == '') )
			$query_array['where'] .= " and (opportunities_cstm.accepted_by_partner_c = '' OR opportunities_cstm.accepted_by_partner_c is null) ";
		
		
		//If the user is searching by the partner account name we need to hack up the query to perform the sorting operation.  This is just a quick fix until we can add back the
		//relationship field once engineering has fixed the initial filters issue.
		if( preg_match('/partner_assigned_to_name/', $order_by) )
		{
			$query_array['from'] .= " LEFT JOIN accounts as partner_accounts on partner_accounts.id = opportunities_cstm.partner_assigned_to_c";
			$query_array['order_by'] = preg_replace("/partner_assigned_to_name/", "partner_accounts.name", $query_array['order_by']  );
		}
		$query_array['select'].=", opportunities_cstm.conflict_c";
		return $query_array;
	}

		/**
	 * Static helper function for getting related account info.
	 */
	public function get_account_detail($opp_id) {
		$ret_array = array();
		$db = DBManagerFactory::getInstance();
		$query = "SELECT acc.id, acc.name, acc.assigned_user_id, acc.billing_address_city account_billing_city, acc.billing_address_state account_billing_state, acc.billing_address_country account_billing_country "
			. "FROM accounts acc, accounts_opportunities a_o "
			. "WHERE acc.id=a_o.account_id"
			. " AND a_o.opportunity_id='$opp_id'"
			. " AND a_o.deleted=0"
			. " AND acc.deleted=0";
		$result = $db->query($query, true,"Error filling in opportunity account details: ");
		$row = $db->fetchByAssoc($result);
		if($row != null) {
			$ret_array['name'] = $row['name'];
			$ret_array['id'] = $row['id'];
			$ret_array['assigned_user_id'] = $row['assigned_user_id'];
			$ret_array['account_billing_city'] = $row['account_billing_city'];
			$ret_array['account_billing_state'] = $row['account_billing_state'];
			$ret_array['account_billing_country'] = $row['account_billing_country'];
		}
		return $ret_array;
	}
	
    	public function get_list_view_data(){
    		global  $current_user;	
    		$seed = new P1_Partners();
    		$the_array = parent::get_list_view_data();
    		$seed->retrieve($the_array['ID']);

    		//Get the language string from the current module so we can localize the customCode Wizard Title. Can't pass in a {MOD.LBL_*} as it won't translate so we do it here for the href name.
      		$p1_part_language = return_module_language($GLOBALS['current_language'], 'P1_Partners');
        	$the_array['LBL_TO_WIZARD_TITLE'] = $p1_part_language['LBL_WIZARD_TITLE'];
		$the_array['LBL_LNK_CLOSED_WON'] = $p1_part_language['LBL_LNK_CLOSED_WON'];
		$the_array['LBL_LNK_DETAIL_VIEW'] = $p1_part_language['LBL_LNK_DETAIL_VIEW'];	
		$the_array['TASKS'] = $this->open_tasks($the_array['ID']);
		$the_array['PARTNER_ASSIGNED_TO_C'] = $this->partner_assigned_to_c;
		$the_array['SIXTYMIN_OPP_C'] = $seed->sixtymin_opp_c;
		$the_array['PARTNER_ASSIGNED_TO_NAME'] = $this->getPartnerName($this->partner_assigned_to_c);
		$ret_values = $this->get_account_detail($the_array['ID']);
		$the_array['ACCOUNT_BILLING_CITY'] = isset($ret_values['account_billing_city']) ? $ret_values['account_billing_city'] : "";
		$the_array['ACCOUNT_BILLING_STATE'] = isset($ret_values['account_billing_state']) ? $ret_values['account_billing_state'] : "";
		$the_array['ACCOUNT_BILLING_COUNTRY'] = isset($ret_values['account_billing_country']) ? $ret_values['account_billing_country'] : "";
		return $the_array;
    	}
    	
	/**
	 * Get the partner/account name given an account id.  
	 *
	 * @param unknown_type $opp_id
	 * @return unknown
	 */
    	public function getPartnerName($account_id)
    	{
    		$results = "";
    		if(empty($account_id))
    			return$results;
    		$db = DBManagerFactory::getInstance();
    		
    		$query = "SELECT name from accounts where id='$account_id' and deleted=0 ";
    		
    		$rs = $db->query($query, true,"Error getting partner/account name");
    		$row = $db->fetchByAssoc($rs);
    		
    		if( !empty($row['name']) )
    			$results = $row['name'];
    		return $results;
    	}
    	
    	// this determines if there are any open tasks
    	public function open_tasks($opp_id) {
    		$opentasks = 0;
    		$db = DBManagerFactory::getInstance();
		$query = "SELECT * FROM tasks WHERE parent_id = '$opp_id' AND status = 'Not Started' and deleted = 0;";
		$result = $db->query($query, true,"Error getting open meetings count: ");
		$opentasks = $db->getRowCount($result);	
		return $opentasks;
    	}

}
?>
