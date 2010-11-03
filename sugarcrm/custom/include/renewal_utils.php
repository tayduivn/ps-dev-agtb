<?php


if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

    $renewal_info = <<<EOQ
<script>
function check_form_special_opp(target_form){

	if(this.document.EditView.sales_stage.value == 'Closed Lost'){

		if(this.document.EditView.closed_lost_reason_c.value==''){
		
			alert('For Closed Lost Opportunities, you must select a Closed Lost Reason');
			return false;
		} 
	
	}
		return check_form(target_form);

}	
</script>
EOQ;


//utilities for lead qual handling
function build_opp_editview(& $focus, & $xtpl){
	
	global $app_list_strings;
	global $json;
	global $renewal_info;
	global $current_user;

	
$popup_request_data = array(
	'call_back_function' => 'set_return',
	'form_name' => 'EditView',
	'field_to_name_array' => array(
		'id' => 'parent_opp_id',
		'name' => 'parent_opp_name',
		),
	);

$encoded_popup_request_data = $json->encode($popup_request_data);
$xtpl->assign('encoded_parent_opp_popup_request_data', $encoded_popup_request_data);	
	

//check to see if the parent_type is opportunities and the parent_id field is not empty -
//if present then we are creating a child opp

	$process_as_child=false;

    if (!empty($_REQUEST['parent_type']) && $_REQUEST['parent_type']=='Opportunities'
    && !empty($_REQUEST['parent_id'])) {
        $focus->parent_opp_id = urldecode($_REQUEST['parent_id']);
        
        $process_as_child = true;
        
       //end if this is a child opp coming from sub-panel 
    }



		$parent_opp = new Opportunity();
		
		if(!empty($focus->parent_opp_id)){
			
			$parent_opp->retrieve($focus->parent_opp_id);
		
			$xtpl->assign("PARENT_OPP_NAME", $parent_opp->name);	
			$xtpl->assign("PARENT_OPP_ID", $focus->parent_opp_id);
			
			
			
			if($process_as_child==true){
				
				$xtpl->assign("ACCOUNT_NAME", $parent_opp->account_name);	
				$xtpl->assign("ACCOUNT_ID", $parent_opp->account_id);

				
				if($parent_opp->team_id=='519912f6-177e-3cb2-ad13-43d9142d7f0f' && $parent_opp->Revenue_Type_c == 'New'){
					$xtpl->assign("ASSIGNED_USER_NAME", 'drew');
					$xtpl->assign("ASSIGNED_USER_ID", '7bed7108-e96b-e3fb-4628-4231355bc253');	
				}	
				
				if($parent_opp->team_id=='519912f6-177e-3cb2-ad13-43d9142d7f0f' && $parent_opp->Revenue_Type_c !='New' &&
				!empty($parent_opp->assigned_user_id)){
					$xtpl->assign("ASSIGNED_USER_NAME", $parent_opp->assigned_user_name);
					$xtpl->assign("ASSIGNED_USER_ID", $parent_opp->assigned_user_id);	
					$xtpl->assign("ASSIGNED_USER_OPTIONS", get_select_options_with_id(get_user_array(TRUE, "Active", $parent_opp->assigned_user_id), $parent_opp->assigned_user_id));
					
				}	
							
				
							
			//end if we should process as child	
			}	
		//end if the parent_opp_id is present	
		}
		
		
		//build special closed lost reason handler script
		
		
		
		
		if(empty($focus->initial_start_c)){
			$xtpl->assign("INITIAL_START_C", $focus->date_closed);
		}
		
		$xtpl->assign("RENEWAL_INFO", $renewal_info);
		
		
		
//deal with initial start date requirements

if($current_user->check_role_membership('Finance')==false && (is_admin($current_user)==false)){
	$xtpl->assign("INITIAL_START_DISABLE", 'disabled');
	if(empty($focus->initial_start_c)){
		$xtpl->assign("INITIAL_START_HIDDEN_VALUE", "<input type='hidden' name='initial_start_c' value='".$focus->date_closed."' >");
	} else {
		$xtpl->assign("INITIAL_START_HIDDEN_VALUE", "<input type='hidden' name='initial_start_c' value='".$focus->initial_start_c."' >");	
	}	
	$xtpl->assign("INITIAL_START_DISABLE_IMG", 'STYLE="visibility:hidden;"');
	 
}
	
		
		

//end function build_lead_editview
}


function build_opp_detailview(& $focus, & $xtpl){


	if(!empty($focus->parent_opp_id)){
		$parent_opp = new Opportunity();
		$parent_opp->retrieve($focus->parent_opp_id);
		
		$xtpl->assign("PARENT_OPP_NAME", $parent_opp->name);	
		$xtpl->assign("PARENT_OPP_ID", $focus->parent_opp_id);
		
	}		

	//end function build_opp_detail_view
}


function build_opp_chainview(& $focus, & $xtpl){
	
	require_once("modules/Opportunities/ChainView.php");
	
	
//end function buil_chainview	
}	

function get_chain_add_select($chain_type, & $focus){
	
	require_once('modules/Accounts/Account.php');
	
	$account = new Account();
	$account->retrieve($focus->account_id);
	
	$rel_opp_list = $account->get_linked_beans('opportunities','Opportunity');
	
	
	$chain_array = array();
	
	foreach($rel_opp_list as $opp){
		
		$process = true;
		
		//exclude if it is self
		if($focus->id == $opp->id){
			$process = false;	
		}	
		
		
		//if type down - exclude if it already is immediately down from focus
		if($process==true){
			$process = chain_check_same($focus, $opp);
		}
		
		
		//if type up - exclude if it already is immediately up from focus
		if($process==true){
			$process = chain_check_same($opp, $focus);
		}
		
		
		//if the process is still true, then add
		if($process==true){
		
			$chain_array[$opp->id] = $opp->name;	
			
		//end if process is true
		}	
		
		
	//end foreach rel_opp_list
	}

	
	return $chain_array;
	
	
	
//end function get_chain_add_select
}	


function chain_check_same(& $up_opp, & $down_opp){
	
	$process = true;
	
	$query = " SELECT count(*) qty_count FROM opps_opps WHERE child_id = '".$down_opp->id."' AND parent_id='".$up_opp->id."'  AND deleted='0' ";
	
	$result = $up_opp->db->query($query, true,"Grabbing Chain Details: ");
	
	$row = $up_opp->db->fetchByAssoc($result);
	
	if($row['qty_count'] > 0 ){
		$process = false;	
	}	
	
	return $process;
	
	
//end function chain_check_same
}	
	
function get_chain_opps(& $focus, $chain_type){
	
	
	$opp_array = array();
	
	if($chain_type=='down'){
		$query = " SELECT child_id target_id, id FROM opps_opps WHERE parent_id='".$focus->id."' AND deleted='0' ";
	} else {
		$query = " SELECT parent_id target_id, id FROM opps_opps WHERE child_id = '".$focus->id."' AND deleted='0' ";
		$parent_array = array();
	}	
	$result = $focus->db->query($query, true,"Grabbing Chain Details: ");
	
	while($row = $focus->db->fetchByAssoc($result)){
	
		$target_opp = new Opportunity();
		$target_opp->retrieve($row['target_id']);
		
		$opp_array[$row['id']] = $target_opp;
		
		if($chain_type=='up'){
			$parent_array[$row['target_id']] = $row['target_id'];
		}	
		
	//end while	
	}
	
	//check for a present upstream_opp_id.  This could occur if you link an upstream opp from the detailview
	if(!empty($focus->upstream_opp_id) && $chain_type=='up'){
	
		//check just to confirm that this opp isnt already in the array
		if(empty($parent_array[$focus->upstream_opp_id])){
			
			$target_opp = new Opportunity();
			$target_opp->retrieve($focus->upstream_opp_id);		
			$opp_array[$focus->upstream_opp_id] = $target_opp;
			
		}	
		
	//end if upstream_opp_id is present and this is type up	
	}	
	
	return $opp_array;
	
//end function get_upstream_opps
}	

function remove_chain_rel(& $focus, $opp_opp_id, $remove_type){

	$query = " UPDATE opps_opps SET deleted='1' WHERE id = '".$opp_opp_id."' ";
	$result = $focus->db->query($query, true,"Removing Relationship: ");
	
	//if you are removing an upstream relationship, save focus again to adjust sub_delta_c
	if($remove_type=='up'){
		$focus->save();
	//end if remove type is up
	}
	//if you are removing a downstream relationship, save that downstream object to adjust sub_delta_c
	if($remove_type=='down'){
		
		//grab the list of downstream relationships
		$query = " SELECT child_id FROM opps_opps WHERE parent_id = '".$focus->id."' AND (deleted='0' OR id='$opp_opp_id') ";
		$result = $focus->db->query($query, true,"Grabbing List to update sub_delta_c: ");		
		
		while($row = $focus->db->fetchByAssoc($result)){
			
			$target_opp = new Opportunity();
			$target_opp->retrieve($row['child_id']);
			$target_opp->save();				
			
		//end while	
		}
	//end if remove type is down		
	}	
//end function remove_chain_rel
}
?>
