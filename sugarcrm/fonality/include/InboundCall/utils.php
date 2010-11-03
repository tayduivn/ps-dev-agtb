<?php
require_once('fonality/include/normalizePhone/utils.php');

global $current_user;

/**********************************************
 * Helper Function for Inbound Call Screen
 *
 * Author: Felix Nilam
 * Date: 20/08/2007
 **********************************************/

// Find records with the specified phone number
// Param: $bean		bean object
// Param: $number	phone number
// Param: $exclude_status	exclude status
// Returns Array(id)
function find_records_with_phone(&$bean, $number, $exclude_status=''){
	$results = array();

	// Get all normalized phone fields
	$phone_fields = getAllPhoneFields($bean);
	$all_fields = array();
	foreach($bean->field_defs as $def){
		$all_fields[] = $def['name'];
	}

	// Generate the phone where array
	$phone_query = array();
	foreach($phone_fields as $field){
		$normalized_field = $field ."_normalized_c";
		if(in_array($normalized_field, $all_fields)){
			$phone_query[] = $field. "_normalized_c = '".$number."'";
		}
	}

	// Generate the query and run it
	$sql = "SELECT id FROM ".$bean->table_name." ";
	// add team filter for SugarPro and SugarEnt
	require('sugar_version.php');
	if($sugar_flavor != 'CE'){
		$bean->add_team_security_where_clause($sql);
	}
	$sql .= " LEFT JOIN ".$bean->table_name."_cstm on id = id_c where deleted = 0 and (".implode(" or ", $phone_query).")";
	if(!empty($exclude_status)){
		$sql .= " and status not in ('".implode("','", $exclude_status)."')";
	}
	
	$res = $bean->db->query($sql);
	while($row = $bean->db->fetchByAssoc($res)){
		$results[] = $row['id'];
	}
	
	return $results;
}

// Find records with specified search string
// Param: $bean
// Param: $search_string
// Returns Array(id)
function find_records_with_string(&$bean, $search_string){
	$results = array();
	
	if($bean->module_dir == 'Contacts' || $bean->module_dir == 'Leads'){
		$name_query = "CONCAT(first_name, ' ', last_name) like '%".$search_string."%'";
	} else {
		$name_query = "name like '%".$search_string."%'";
	}
	$sql = "SELECT id FROM ".$bean->table_name." ";
	// add team filter for SugarPro and SugarEnt
	require('sugar_version.php');
	if($sugar_flavor != 'CE'){
		$bean->add_team_security_where_clause($sql);
	}
	$sql .= " where deleted = 0 and $name_query";
	
	$res = $bean->db->query($sql);
	while($row = $bean->db->fetchByAssoc($res)){
                $results[] = $row['id'];
        }

	return $results;
}

// Parse the template for specified records
// Param: $tpl		xtemplate resource
// Param: $bean_ids	bean ids
// Param: $bean		bean object
function parse_template_record(&$tpl, $bean_ids, $bean, $tpl_var = ''){
	global $app_list_strings;
	global $beanList;
	global $beanFiles;
	global $inbound_call_config;
	global $theme;
	global $call_assistant_form;
	$themepath = "themes/".$theme."/images/";

	$attach_call_icon = '';
	$create_new_icon = '';
	$convert_icon = '';
	$attach_icon = '<img src="fonality/include/images/arrow2.png" border="0" style="vertical-align: middle">';
	
	$log = '';
	$beans = array();
	foreach($bean_ids as $bean_id){
		$copy_bean = new $bean->object_name();
		$copy_bean->retrieve($bean_id);
		$copy_bean->fill_in_additional_detail_fields();
		$tmp_name = $copy_bean->name;
		if($copy_bean->object_name == "Account" || $copy_bean->object_name == "Call" || $copy_bean->object_name == "aCase" || $copy_bean->object_name == "Opportunity"){
			$name = $copy_bean->name;
		} else {
			$name = $copy_bean->full_name;
		}
		
		$log .= "\n$name($bean_id)";
		$copy_bean->name = "<a href='index.php?module=".$copy_bean->module_dir."&action=DetailView&record=".$copy_bean->id."' target='_blank'>$name</a>";
	
		if($copy_bean->object_name == 'Account'){
			$copy_bean->industry = $app_list_strings['industry_dom'][$copy_bean->industry];
			$copy_bean->account_type = $app_list_strings['account_type_dom'][$copy_bean->account_type];
		}
		if(!empty($copy_bean->description)){
			$copy_bean->description = str_replace(array("\r\n", "\n"), array("",""), nl2br(url2html(htmlentities($copy_bean->description))));
		}
		
		// set the new call link (available only on ENT edition)
		if($inbound_call_config['version'] == "ENT"){
			$copy_bean->new_call_link = '<table cellpadding="0" cellspacing="0" border="0"><tr><td style="border-bottom: 0px none" align="left" width="10%" nowrap>';
			
			if($copy_bean->module_dir != 'Calls'){
				// if this is a contact with related account
                // automatically link to that account
                if($copy_bean->module_dir == 'Contacts' && !empty($copy_bean->account_id)){
                        $copy_bean->new_call_link .= $attach_icon .
                                ' <a href="javascript:void(0);" style="width:80px"'.
                                ' onClick="document.'.$call_assistant_form.'.type.value=\'Calls\';'.
                                'document.'.$call_assistant_form.'.parent_type.value=\'Accounts\';'.
                                'document.'.$call_assistant_form.'.parent_id.value=\''.$copy_bean->account_id.'\';'.
                                'document.'.$call_assistant_form.'.contact_id.value=\''.$copy_bean->id.'\';'.
                                'document.'.$call_assistant_form.'.submit();" >this '.$copy_bean->object_name.'</a>';
                } else {
					$copy_bean->new_call_link .= $attach_icon .
								' <a href="javascript:void(0);" style="width:80px" '.
								'onClick="document.'.$call_assistant_form.'.type.value=\'Calls\';'.
								'document.'.$call_assistant_form.'.parent_type.value=\''.$copy_bean->module_dir.'\';'.
								'document.'.$call_assistant_form.'.parent_id.value=\''.$copy_bean->id.'\';'.
								'document.'.$call_assistant_form.'.submit();" >this '.$copy_bean->object_name.'</a>';
                }
				$copy_bean->new_call_link .= "</td></tr>";

				$cases = 0;
				$project = 0;
				$opportunities = 0;
				$contacts = 0;
	
				if($copy_bean->module_dir != 'Leads'){
					$cases = 1;
					$project = 0;
					$opportunities = 1;
				}
			} else {
				$copy_bean->new_call_link .= $attach_icon .' <a href="javascript:void(0);" style="width:80px" onClick="document.'.$call_assistant_form.'.type.value=\'Planned\';document.'.$call_assistant_form.'.parent_type.value=\''.$copy_bean->module_dir.'\';document.'.$call_assistant_form.'.parent_id.value=\''.$copy_bean->id.'\';document.'.$call_assistant_form.'.submit();" >this '.$copy_bean->object_name.'</a>';
				$copy_bean->new_call_link .= "</td></tr>";
			}
			if($copy_bean->module_dir == 'Accounts'){
				$contacts = 1;
			}

			if($cases || $project || $opportunities || $contacts){
				$copy_bean->new_call_link .= '<tr><td align="left" style="border-bottom: 0px none" width="10%" nowrap>';
				$copy_bean->new_call_link .= $attach_icon .' <a href="javasccript:void(0);" style="width:80px" id="'.$copy_bean->id.'" onClick="return create_new_overlib(\''.$copy_bean->id.'\',\'Sugar\',\''.$call_assistant_form.'\',\''.$copy_bean->module_dir.'\',\''.$opportunities.'\',\''.$cases.'\',\''.$project.'\',\''.$contacts.'\');" >new record (for this '.$copy_bean->object_name.')</a>';
				$copy_bean->new_call_link .= "</td></tr>";
			}
			
			$copy_bean->new_call_link .= "</table>";
		}
		
		// Set the overlib
		$copy_bean->overlib = "<a href=\'index.php?module=".$copy_bean->module_dir."&action=DetailView&record=".$copy_bean->id."\' target=\'_blank\'>".htmlentities($copy_bean->name)."</a><br><br>Description:<br>".strip_tags($copy_bean->description);

		$beans[] = $copy_bean;
	}
	$smarty_var = strtoupper($bean->module_dir);
	if(!empty($tpl_var)){
		$smarty_var = $tpl_var;
	}
	$tpl->assign($smarty_var, $beans);
	logUAE('callassistant', "Matching $smarty_var:$log\n");
}

// Parse the template for specified records
// Param: $tpl		template resource
// Param: $bean_ids	array(array(bean_ids))
// Param: $bean		bean object
// Param: $typ_val	template var override
// Param: $parent_type	the parent record type
// Param: $edit		1 display edit link, 0 otherwise
function parse_template_record_multi(&$tpl, $bean_ids, $bean, $tpl_var = '', $parent_type='', $edit=0){
	global $app_list_strings;
	global $beanList;
	global $beanFiles;
	global $inbound_call_config;
	global $theme;
	global $timedate;
	global $call_assistant_form;
	$themepath = "themes/".$theme."/images/";

	$attach_call_icon = '';
	$create_new_icon = '';
	$convert_icon = '';
	$attach_icon = '<img src="fonality/include/images/arrow2.png" border="0" style="vertical-align: middle">';
	
	$results = array();
	foreach($bean_ids as $key => $val){
	$beans = array();

	$log = '';
	foreach($val as $bean_id){
		$copy_bean = new $beanList[$bean->module_dir]();
		$copy_bean->retrieve($bean_id);
		$copy_bean->fill_in_additional_detail_fields();
		$copy_bean->date_entered = $timedate->to_display_date($copy_bean->date_entered);
		$tmp_name = $copy_bean->name;
		if($copy_bean->object_name == "Account" || $copy_bean->object_name == "Call" || $copy_bean->object_name == "Case" || $copy_bean->object_name == "Opportunity"){
			$name = $copy_bean->name;
		} else {
			$name = $copy_bean->full_name;
		}
		$log .= "\n$name($bean_id)";

		$copy_bean->name = "<a href='index.php?module=".$copy_bean->module_dir."&action=DetailView&record=".$copy_bean->id."' target='_blank'>$name</a>";
		if($edit){
			$copy_bean->name .= " <input type=\"button\" class=\"button\" name=\"editlink\" value=\"Edit\" onClick=\"window.location.href='index.php?module=".$copy_bean->module_dir."&action=EditView&record=".$copy_bean->id."'\">";
		}
	
		if($copy_bean->object_name == 'Account'){
			$copy_bean->industry = $app_list_strings['industry_dom'][$copy_bean->industry];
			$copy_bean->account_type = $app_list_strings['account_type_dom'][$copy_bean->account_type];
		}
		if(!empty($copy_bean->description)){
			$copy_bean->description = str_replace(array("\r\n", "\n"), array("",""), nl2br(url2html(htmlentities($copy_bean->description))));
		}
		$copy_bean->amount = currency_format_number($copy_bean->amount);
		// set the new call link
		if($inbound_call_config['version'] == "ENT"){
			$copy_bean->new_call_link = '<table cellpadding="0" cellspacing="0" border="0"><tr><td align="left" style="border-bottom: 0px none" width="10%" nowrap>';
			if($parent_type == 'Contacts'){
				$contact_id = $key;
			} else {
				$contact_id = '';
			}
			
			// if this is a contact with related account
            // automatically link to that account
            if($copy_bean->module_dir == 'Contacts' && !empty($copy_bean->account_id)){
                    $copy_bean->new_call_link .= $attach_icon .
                            ' <a href="javascript:void(0);" style="width:80px"'.
                            ' onClick="document.'.$call_assistant_form.'.type.value=\'Calls\';'.
                            'document.'.$call_assistant_form.'.parent_type.value=\'Accounts\';'.
                            'document.'.$call_assistant_form.'.parent_id.value=\''.$copy_bean->account_id.'\';'.
                            'document.'.$call_assistant_form.'.contact_id.value=\''.$copy_bean->id.'\';'.
                            'document.'.$call_assistant_form.'.submit();" >this '.$copy_bean->object_name.'</a>';
            } else {
				$copy_bean->new_call_link .= $attach_icon .
						' <a href="javascript:void(0);" style="width:80px" '.
						'onClick="document.'.$call_assistant_form.'.type.value=\'Calls\';'.
						'document.'.$call_assistant_form.'.parent_type.value=\''.$copy_bean->module_dir.'\';'.
						'document.'.$call_assistant_form.'.parent_id.value=\''.$copy_bean->id.'\';'.
						'document.'.$call_assistant_form.'.contact_id.value=\''.$contact_id.'\';'.
						'document.'.$call_assistant_form.'.submit();" >this '.$copy_bean->object_name.'</a>';
            }
			$copy_bean->new_call_link .= '</td></tr>';
			
			if($copy_bean->module_dir == 'Contacts'){
				$cases = 1;
				$project = 0;
				$opportunities = 1;
				$contacts = 0;
				$copy_bean->new_call_link .= '<tr><td align="left" style="border-bottom: 0px none" width="10%" nowrap>';
				$copy_bean->new_call_link .= $attach_icon .' <a href="javascript:void(0);" style="width:80px" id="'.$copy_bean->id.'" onClick="return create_new_overlib(\''.$copy_bean->id.'\',\'Sugar\',\''.$call_assistant_form.'\',\''.$copy_bean->module_dir.'\',\''.$opportunities.'\',\''.$cases.'\',\''.$project.'\',\''.$contacts.'\');" >new record (for this '.$copy_bean->object_name.')</a>';
				$copy_bean->new_call_link .= '</td></tr>';
			}
			$copy_bean->new_call_link .= '</table>';
		}

		// Set the overlib
		$copy_bean->overlib = "<a href=\'index.php?module=".$copy_bean->module_dir."&action=DetailView&record=".$copy_bean->id."\' target=\'_blank\'>".htmlentities($copy_bean->name)."</a><br><br>Description:<br>".strip_tags($copy_bean->description);

		$beans[] = $copy_bean;
	}
	
	$results[$key] = $beans;
	}

	$smarty_var = strtoupper($bean->module_dir);
	if(!empty($tpl_var)){
		$smarty_var = $tpl_var;
	}
	$tpl->assign($smarty_var, $results);
	logUAE('callassistant', "Matching $smarty_var:$log\n");
}

// Find any planned calls for the specified records
// Param: $bean_ids	the array of record ids
// Param: $type		the module type
// Param: $period	time period before now to be included in the results
// Returns: Array(array(call_ids))
function find_planned_calls($bean_ids, $type, $period){
	global $beanList;
	global $beanFiles;
	require_once($beanFiles[$beanList[$type]]);
	$bean = new $beanList[$type]();
	
	$planned_calls = array();
	if(empty($bean_ids)){
		return $planned_calls;
	}
	$before_date = gmdate("Y-m-d H:i:s", time() + $period);
	if($type == 'Contacts'){
		$query = "SELECT cc.contact_id as mykey, c.id FROM calls c LEFT JOIN calls_contacts cc on c.id = cc.call_id ";
				
	} else {
		$query = "SELECT parent_id as mykey, c.id FROM calls c ";
	}

	// add team filter for SugarPro and SugarEnt
	require('sugar_version.php');
	if($sugar_flavor != 'CE'){
		require_once('modules/Calls/Call.php');
		$ca = new Call();
		$ca->add_team_security_where_clause($query, 'c');
	}
	if($type == 'Contacts'){
		$query .= " WHERE c.status = 'Planned' and c.deleted = 0 and cc.deleted = 0 and cc.contact_id in ('".implode("','",$bean_ids)."')";
	} else {
		$query .= " WHERE c.status = 'Planned' and c.deleted = 0 and parent_type = '".$type."' and parent_id in ('".implode("','",$bean_ids)."')";
	}
	
	if($period != 'All'){
		$query .= " and c.date_start <= '".$before_date."'";
	}

	// add team filter for SugarPro and SugarEnt
	require('sugar_version.php');
	if($sugar_flavor != 'CE'){
		global $current_user;
		if(!is_admin($current_user)){
			$my_teams = $current_user->get_my_teams();
			$query .= " and c.team_id in ('".implode("','", array_keys($my_teams))."')";
		}
	}

	$res = $bean->db->query($query);
	$tmp_array = array();
	while($row = $bean->db->fetchByAssoc($res)){
		if(empty($planned_calls[$row['mykey']])){
			$planned_calls[$row['mykey']] = array($row['id']);
		} else {
			$planned_calls[$row['mykey']] = array_merge($planned_calls[$row['mykey']], array($row['id']));
		}
	}
	
	return $planned_calls;
}

function find_planned_related_calls($related_ids, $related_type, $period){
	$related_planned_calls = array();
	foreach($related_ids as $bean_id => $tmp_array){
		$related_calls = find_planned_calls($tmp_array, $related_type, $period);
		$related_call_ids = array();
		foreach($related_calls as $key => $val){
			foreach($val as $call_id){
				$related_call_ids[] = $call_id;
			}
		}
		$related_planned_calls = array_merge($related_planned_calls, array($bean_id => $related_call_ids));
	}
	
	return $related_planned_calls;
}
	

// Find any related Opportunities for the specified records
// Param: $bean_ids	the array of record ids
// Param: $type		the module type
// Param: $exclude_status	array of status to be excluded
// Returns: Array(array(opp_ids))
function find_related_opportunities($bean_ids, $type, $exclude_status){
	global $beanList;
	global $beanFiles;
	require_once($beanFiles[$beanList[$type]]);
	$bean = new $beanList[$type]();
	
	$opps = array();
	if(empty($bean_ids)){
		return $opps;
	}
	if($type == 'Contacts'){
		$opp_rel_table = "opportunities_contacts";
		$rel_key = "contact_id";
	} else if($type == 'Accounts'){
		$opp_rel_table = "accounts_opportunities";
		$rel_key = "account_id";
	}
	
	$query = "SELECT rel.$rel_key as mykey, opp.id FROM opportunities opp LEFT JOIN $opp_rel_table rel on opp.id = rel.opportunity_id ";

	// add team filter for SugarPro and SugarEnt
	require('sugar_version.php');
	if($sugar_flavor != 'CE'){
		require_once('modules/Opportunities/Opportunity.php');
		$opp = new Opportunity();
		$opp->add_team_security_where_clause($query, 'opp');
	}

	$query .= " WHERE opp.sales_stage not in ('".implode("','",$exclude_status)."') and opp.deleted = 0 and rel.deleted = 0 and rel.$rel_key in ('".implode("','",$bean_ids)."')";
	$res = $bean->db->query($query);
	while($row = $bean->db->fetchByAssoc($res)){
		if(empty($opps[$row['mykey']])){
			$opps[$row['mykey']] = array($row['id']);
		} else {
			$opps[$row['mykey']] = array_merge($opps[$row['mykey']], array($row['id']));
		}
	}
	
	return $opps;
}

// Find any related Cases for the specified records
// Param: $bean_ids	the array of record ids
// Param: $type		the module type
// Param: $exclude_status	array of status to be excluded
// Returns: Array(array(case_ids))
function find_related_cases($bean_ids, $type, $exclude_status){
	global $beanList;
	global $beanFiles;
	require_once($beanFiles[$beanList[$type]]);
	$bean = new $beanList[$type]();
	
	$cases = array();
	if(empty($bean_ids)){
		return $cases;
	}
	if($type == 'Contacts'){
		$query = "SELECT cc.contact_id as mykey, ca.id from cases ca LEFT JOIN contacts_cases cc on ca.id = cc.case_id ";
	} else if($type == 'Accounts'){
		$query = "SELECT ca.account_id as mykey, ca.id from cases ca ";
	}
	
	// add team filter for SugarPro and SugarEnt
	require('sugar_version.php');
	if($sugar_flavor != 'CE'){
		require_once('modules/Cases/Case.php');
		$case = new aCase();
		$case->add_team_security_where_clause($query, 'ca');
	}
	
	if($type == 'Contacts'){
		$query .= " WHERE ca.status not in ('".implode("','",$exclude_status)."') and ca.deleted = 0 and cc.deleted = 0 and cc.contact_id in ('".implode("','",$bean_ids)."')";
	} else {
		$query .= " WHERE ca.status not in ('".implode("','",$exclude_status)."') and ca.deleted = 0 and ca.account_id in ('".implode("','",$bean_ids)."')";
	}
	$res = $bean->db->query($query);
	while($row = $bean->db->fetchByAssoc($res)){
		if(empty($cases[$row['mykey']])){
			$cases[$row['mykey']] = array($row['id']);
		} else {
			$cases[$row['mykey']] = array_merge($cases[$row['mykey']], array($row['id']));
		}
	}
	return $cases;
}

// Find related Contacts for specified account_ids
// Param: $account_ids
// Returns: Array(array(contact_ids))
function find_related_contacts($account_ids){
	require_once('modules/Accounts/Account.php');
	$acct = new Account();
	$contacts = array();
	if(empty($account_ids)){
		return $contacts;
	}
	$query = "SELECT ac.id as mykey, co.id from accounts ac left join accounts_contacts acon on acon.account_id = ac.id left join contacts co on co.id = acon.contact_id ";
	// add team filter for SugarPro and SugarEnt
	require('sugar_version.php');
	if($sugar_flavor != 'CE'){
		require_once('modules/Contacts/Contact.php');
		$con = new Contact();
		$con->add_team_security_where_clause($query, 'co');
	}
	
	$query .= " WHERE acon.deleted = 0 and co.deleted = 0 and ac.deleted = 0 and ac.id in ('".implode("','",$account_ids)."')";
	
	$db = DBManagerFactory::getInstance();
	$res = $db->query($query);
	while($row = $db->fetchByAssoc($res)){
		if(empty($contacts[$row['mykey']])){
			$contacts[$row['mykey']] = array($row['id']);
		} else {
			$contacts[$row['mykey']] = array_merge($contacts[$row['mykey']], array($row['id']));
		}
	}
	
	return $contacts;
}

// Find related Accounts for specified 
// Param: $bean_ids
// Param: $type (Contacts or Leads)
// Returns: Array(account_ids)
function find_related_accounts($bean_ids, $type){
	$accounts = array();
	if(empty($bean_ids)){
		return $accounts;
	}
	require_once('modules/Accounts/Account.php');
	$acct = new Account();
	
	if($type == "Contacts"){
		$query = "SELECT co.id as mykey, ac.id as accountid from contacts co left join accounts_contacts acon on co.id = acon.contact_id left join accounts ac on ac.id = acon.account_id ";
	} else if($type == "Leads"){
		$query = "SELECT le.id as mykey, le.account_id as accountid from leads le left join accounts ac on le.account_id = ac.id ";
	}
	
	// add team filter for SugarPro and SugarEnt
	require('sugar_version.php');
	if($sugar_flavor != 'CE'){
		$acct->add_team_security_where_clause($query, 'ac');
	}
	
	if($type == "Contacts"){
		$query .= " where acon.deleted = 0 and ac.deleted = 0 and co.deleted = 0 and co.id in ('".implode("','",$bean_ids)."')";
	} else {
		$query .= " where le.deleted = 0 and ac.deleted = 0 and le.id in ('".implode("','",$bean_ids)."')";
	}
	
	$db = DBManagerFactory::getInstance();
	$res = $db->query($query);
	while($row = $db->fetchByAssoc($res)){
		if(!in_array($row['accountid'], $accounts)){
			$accounts[] = $row['accountid'];
		}
	}

	return $accounts;
}
?>
