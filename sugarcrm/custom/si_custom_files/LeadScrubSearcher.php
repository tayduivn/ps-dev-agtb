<?php

class LeadScrubSearcher {
	private $label_map = array(
		'first_last_email' => 'First Name, Last Name, Email Address/Domain',
		'last_email' => 'Last Name, Email Address/Domain',
		'first_email' => 'First Name, Email Address/Domain',
		'firstinitial_email' => 'First Initial, Email Address/Domain',
		'email' => 'Email Address/Domain',
		'accountname' => 'Account Name',
		'website' => 'Account Website from Email Domain',
		'first_last' => 'First Name, Last Name (with no email provided)',
		'first' => 'First Name (with no email provided)',
		'last' => 'Last Name (with no email provided)',
	);
	

public function getRecordIds($lead_id, $email, $domain_only = true, $first_name = '', $last_name = '', $account_name = '', $limit = 0){
	require_once('modules/Leads/Lead.php');
	$lead_seed = new Lead();
	//require_once('modules/LeadAccounts/LeadAccount.php');
	//$leadaccount_seed = new LeadAccount();
	require_once('modules/Contacts/Contact.php');
	$contact_seed = new Contact();
	require_once('modules/Accounts/Account.php');
	$account_seed = new Account();
	
	$do_account_search = true;
	$email_domain = $email;
	if(!$domain_only){
		$at_pos = strpos($email, '@');
		if($at_pos !== false){
			$email_domain = substr($email, $at_pos + 1); 
		}
	}
	
	$return_ids = array();
    // jwhitcraft customization - Remove LeadContacts and LeadAccounts
	//$return_ids['LeadContacts'] = $this->getLeadIds($lead_id, $email, $domain_only, $first_name, $last_name, $account_name, $limit, $lead_seed);
	//$return_ids['LeadAccounts'] = $this->getLeadAccountIds($email_domain, $account_name, $limit, $leadaccount_seed);
    // end jwhitcraft customization
	$return_ids['Contacts'] = $this->getContactIds($email, $domain_only, $first_name, $last_name, $account_name, $limit, $contact_seed);
	$return_ids['Accounts'] = $this->getAccountIds($email_domain, $account_name, $limit, $account_seed);
	
	return $return_ids;
}

public function getInString($record_id_array){
	$in_string = "";
	foreach($record_id_array as $index => $section){
		if(is_array($section)){
			$in_string .= "'".implode("', '", $section)."', ";
		}
		else{
			$in_string .= "'$section', ";
		}
	}
	$in_string = substr($in_string, 0, -2);
	if(empty($in_string)){
		$in_string = "''";
	}
	$in_string = "($in_string)";
	return $in_string;
}

public function getSmartSearchDefinitions(){
	return $this->label_map;
}

public function translateLabel($label){
	$translated = $label;
	
	if(array_key_exists($label, $this->label_map)){
		$translated = $this->label_map[$label];
	}
	
	return $translated;
}

private function getLeadIds($lead_id, $email, $domain_only, $first_name, $last_name, $account_name, $limit, $seed){
	$leads_query = 
	"select leadcontacts.id 'id'\n".
	"from leadcontacts\n".//inner join leadcontacts_cstm on leadcontacts.id = leadcontacts_cstm.id_c\n".
	"           left join email_addr_bean_rel on leadcontacts.id = email_addr_bean_rel.bean_id and\n". //email_addr_bean_rel.bean_module = 'Leads' and\n". //SADEK TODO: 'Leads' to 'LeadContacts'
	"                      email_addr_bean_rel.deleted = 0\n".
	"           left join email_addresses on email_addr_bean_rel.email_address_id = email_addresses.id and email_addresses.deleted = 0\n".
	"           left join leadaccounts on leadcontacts.leadaccount_id = leadaccounts.id and leadaccounts.deleted = 0\n";
	$seed->add_team_security_where_clause($leads_query);
	$leads_query .=
	"\nwhere replace_where_clauses_here\n".
	"  and leadcontacts.id != '$lead_id'\n".
	"  and leadcontacts.deleted = 0\n";
	
	$record_ids = array();
	$searches_array = array();
	$domain_field = $domain_only ? 'email_addresses.email_address_domain' : 'email_addresses.email_address';
	if(!empty($first_name) && !empty($email)){
		$searches_array["first_last_email"] = "leadcontacts.first_name like '$first_name' and leadcontacts.last_name like '$last_name' and $domain_field like '$email'";
	}
	if(!empty($last_name) && !empty($email)){
		$searches_array['last_email'] = "leadcontacts.last_name like '$last_name' and $domain_field like '$email'";
	}
	if(!empty($first_name) && !empty($email)){
		$searches_array['first_email'] = "leadcontacts.first_name like '$first_name' and $domain_field like '$email'";
	}
	if(!empty($first_name) && !empty($email)){
		$searches_array['firstinitial_email'] = "leadcontacts.first_name like '".$first_name[0]."%' and $domain_field like '$email'";
	}
	if(!empty($email)){
		$searches_array['email'] = "$domain_field like '$email'";
	}
	if(empty($email)){
		if(!empty($first_name) && !empty($last_name)){
			$searches_array['first_last'] = "leadcontacts.first_name like '$first_name' and leadcontacts.last_name like '$last_name'";
		}
		else if(!empty($last_name)){
			$searches_array['last'] = "leadcontacts.last_name like '$last_name'";
		}
		else if(!empty($first_name)){
			$searches_array['first'] = "leadcontacts.first_name like '$first_name'";
		}
	}
	if(!empty($account_name)){
		$searches_array['accountname'] = "leadaccounts.name like '$account_name'";
	}
	
	$counter = 0;
	$done = false;
	foreach($searches_array as $label => $where_clause){
		$this_query = str_replace("replace_where_clauses_here", $where_clause, $leads_query);
		//siLogThis('lead_finder_functions.log', $this_query);
		$this_res = $GLOBALS['db']->query($this_query, true);
		while($row = $GLOBALS['db']->fetchByAssoc($this_res)){
			$continue = false;
			foreach($record_ids as $label_array){
				if(in_array($row['id'], $label_array)){
					$continue = true;
				}
			}
			if($continue){
				continue;
			}
			$counter++;
			$record_ids[$label][] = $row['id'];
			if($limit != 0 && $counter == $limit){
				$done = true;
				break;
			}
		}
		if($done){
			break;
		}
	}
	
	return $record_ids;
}

private function getLeadAccountIds($email_domain, $account_name, $limit, $seed){
	$leadaccounts_query = 
	"select leadaccounts.id 'id'\n".
	"from leadaccounts \n".
	"              left join email_addr_bean_rel on leadaccounts.id = email_addr_bean_rel.bean_id and email_addr_bean_rel.deleted = 0\n".
	"              left join email_addresses on email_addr_bean_rel.email_address_id = email_addresses.id and email_addresses.deleted = 0\n";
	$seed->add_team_security_where_clause($leadaccounts_query);
	$leadaccounts_query .=
	"\nwhere replace_where_clauses_here\n".
	"  and leadaccounts.deleted = 0\n";
	
	$record_ids = array();
	$searches_array = array();
	require_once('custom/si_custom_files/custom_functions.php');
	if(!empty($email_domain) && !inDomainExclusionList($email_domain)){
		$searches_array['website'] = "(leadaccounts.website like 'http://www.$email_domain%' OR leadaccounts.website like '$email_domain%' OR leadaccounts.website like 'www.$email_domain%' OR leadaccounts.website like 'https://www.$email_domain%')";
		$searches_array['email'] = "email_addresses.email_address_domain = '$email_domain'";
	}
	if(!empty($account_name)){
		$searches_array['accountname'] = "leadaccounts.name like '$account_name%'";
	}
	
	$counter = 0;
	$done = false;
	foreach($searches_array as $label => $where_clause){
		$this_query = str_replace("replace_where_clauses_here", $where_clause, $leadaccounts_query);
		//siLogThis('lead_finder_functions.log', $this_query);
		$this_res = $GLOBALS['db']->query($this_query, true);
		while($row = $GLOBALS['db']->fetchByAssoc($this_res)){
			$continue = false;
			foreach($record_ids as $label_array){
				if(in_array($row['id'], $label_array)){
					$continue = true;
				}
			}
			if($continue){
				continue;
			}
			$counter++;
			$record_ids[$label][] = $row['id'];
			if($limit != 0 && $counter == $limit){
				$done = true;
				break;
			}
		}
		if($done){
			break;
		}
	}
	
	return $record_ids;
}

private function getContactIds($email, $domain_only, $first_name, $last_name, $account_name, $limit, $seed){
	$contacts_query = 
	"select contacts.id 'id'\n".
	"from contacts inner join contacts_cstm on contacts.id = contacts_cstm.id_c\n".
	"              left join email_addr_bean_rel on contacts.id = email_addr_bean_rel.bean_id and email_addr_bean_rel.bean_module = 'Contacts' and\n".
	"                         email_addr_bean_rel.deleted = 0\n".
	"              left join email_addresses on email_addr_bean_rel.email_address_id = email_addresses.id and email_addresses.deleted = 0\n".
	"              left join accounts_contacts on accounts_contacts.contact_id = contacts.id and accounts_contacts.deleted = 0\n".
	"              left join accounts on accounts_contacts.account_id = accounts.id and accounts.deleted = 0\n";
	$seed->add_team_security_where_clause($contacts_query);
	$contacts_query .=
	"\nwhere replace_where_clauses_here\n".
	"  and contacts.deleted = 0\n";
	
	$record_ids = array();
	$searches_array = array();
	$domain_field = $domain_only ? 'email_addresses.email_address_domain' : 'email_addresses.email_address';
	if(!empty($first_name) && !empty($email)){
		$searches_array["first_last_email"] = "contacts.first_name like '$first_name' and contacts.last_name like '$last_name' and $domain_field like '$email'";
	}
	if(!empty($last_name) && !empty($email)){
		$searches_array['last_email'] = "contacts.last_name like '$last_name' and $domain_field like '$email'";
	}
	if(!empty($first_name) && !empty($email)){
		$searches_array['first_email'] = "contacts.first_name like '$first_name' and $domain_field like '$email'";
	}
	if(!empty($first_name) && !empty($email)){
		$searches_array['firstinitial_email'] = "contacts.first_name like '".$first_name[0]."%' and $domain_field like '$email'";
	}
	if(!empty($email)){
		$searches_array['email'] = "$domain_field like '$email'";
	}
	if(empty($email)){
		if(!empty($first_name) && !empty($last_name)){
			$searches_array['first_last'] = "contacts.first_name like '$first_name' and contacts.last_name like '$last_name'";
		}
		else if(!empty($last_name)){
			$searches_array['last'] = "contacts.last_name like '$last_name'";
		}
		else if(!empty($first_name)){
			$searches_array['first'] = "contacts.first_name like '$first_name'";
		}
	}
	if(!empty($account_name)){
		$searches_array['accountname'] = "accounts.name like '$account_name%'";
	}
	
	$counter = 0;
	$done = false;
	foreach($searches_array as $label => $where_clause){
		$this_query = str_replace("replace_where_clauses_here", $where_clause, $contacts_query);
		//siLogThis('lead_finder_functions.log', $this_query);
		$this_res = $GLOBALS['db']->query($this_query, true);
		while($row = $GLOBALS['db']->fetchByAssoc($this_res)){
			$continue = false;
			foreach($record_ids as $label_array){
				if(in_array($row['id'], $label_array)){
					$continue = true;
				}
			}
			if($continue){
				continue;
			}
			$counter++;
			$record_ids[$label][] = $row['id'];
			if($limit != 0 && $counter == $limit){
				$done = true;
				break;
			}
		}
		if($done){
			break;
		}
	}
	
	return $record_ids;
}

private function getAccountIds($email_domain, $account_name, $limit, $seed){
	$accounts_query = 
	"select accounts.id 'id'\n".
	"from accounts inner join accounts_cstm on accounts.id = accounts_cstm.id_c\n".
	"              left join email_addr_bean_rel on accounts.id = email_addr_bean_rel.bean_id and email_addr_bean_rel.bean_module = 'Accounts' and\n".
	"                         email_addr_bean_rel.deleted = 0\n".
	"              left join email_addresses on email_addr_bean_rel.email_address_id = email_addresses.id and email_addresses.deleted = 0\n";
	$seed->add_team_security_where_clause($accounts_query);
	$accounts_query .=
	"\nwhere replace_where_clauses_here\n".
	"  and accounts.deleted = 0\n";
	
	$record_ids = array();
	$searches_array = array();
	require_once('custom/si_custom_files/custom_functions.php');
	if(!empty($email_domain) && !inDomainExclusionList($email_domain)){
		$searches_array['website'] = "(accounts.website like 'http://www.$email_domain%' OR accounts.website like '$email_domain%' OR accounts.website like 'www.$email_domain%' OR accounts.website like 'https://www.$email_domain%')";
		$searches_array['email'] = "email_addresses.email_address_domain = '$email_domain'";
	}
	if(!empty($account_name)){
		$searches_array['accountname'] = "accounts.name like '$account_name%'";
	}
	
	$counter = 0;
	$done = false;
	foreach($searches_array as $label => $where_clause){
		$this_query = str_replace("replace_where_clauses_here", $where_clause, $accounts_query);
		//siLogThis('lead_finder_functions.log', $this_query);
		$this_res = $GLOBALS['db']->query($this_query, true);
		while($row = $GLOBALS['db']->fetchByAssoc($this_res)){
			$continue = false;
			foreach($record_ids as $label_array){
				if(in_array($row['id'], $label_array)){
					$continue = true;
				}
			}
			if($continue){
				continue;
			}
			$counter++;
			$record_ids[$label][] = $row['id'];
			if($limit != 0 && $counter == $limit){
				$done = true;
				break;
			}
		}
		if($done){
			break;
		}
	}
	
	return $record_ids;
}

}
