<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

// called from LayoutDefs/AccountMemberRecords as external function
function account_member_records($param) {

	// load parent bean
	global $beanList;
	$parent_module = $beanList[$_REQUEST['module']];
	$focus = new $parent_module();
	$focus->retrieve($_REQUEST['record']);

		/** 
		 * Three types of relationships are handled for the subpanel module:
		 * -> using a relationship table
		 * -> specific code for linkedemails
		 * -> using a parent_id and parent_type
		 */

		// init query strings
		$query_where = '';
		$query_join1 = '';
		$query_join2 = '';

		// fetch child accounts and build where/join queries
		$sql = 'SELECT acc.id FROM accounts acc WHERE acc.parent_id = "'.$focus->id.'" AND acc.deleted = 0';
		$q_child = $focus->db->query($sql);
		while($child = $focus->db->fetchByAssoc($q_child)) {
			if(isset($param['relationship_table'])) {
				$query_join1 .= 'OR '.$param['relationship_table'].'.'.$param['account_col'].' = "'.$child['id'].'" ';
			} elseif($param['subpanel_module'] == 'linkedemails') {
				$query_join1 .= 'OR eabr.bean_id = "'.$child['id'].'" ';
				$query_join2 .= 'OR eb.bean_id = "'.$child['id'].'" ';
			} else {
				$query_where .= 'OR '.$param['subpanel_module'].'.parent_id = "'.$child['id'].'" ';
			} 
		}

		// build query array
		$query_array = array();

		// with relationship table
		if(isset($param['relationship_table'])) {

			$query_array['select'] = 'SELECT '.$param['subpanel_module'].'.id';
			$query_array['from'] = 'FROM '.$param['subpanel_module'];
			$query_array['where'] = 'WHERE '.$param['relationship_table'].'.deleted=0 AND '.$param['subpanel_module'].'.deleted = 0';
			$query_array['join'] = ' INNER JOIN '.$param['relationship_table'].' 
										ON ('.$param['subpanel_module'].'.id = '.$param['relationship_table'].'.'.$param['related_col'].' 
										AND ('.$param['relationship_table'].'.'.$param['account_col'].' = "'.$focus->id.'" '.$query_join1.'))';
			$query_array['join_tables'] = array($param['relationship_table']);

		// special case for linkedemails
		} elseif($param['subpanel_module'] == 'linkedemails') {

			$query_array['select'] = 'SELECT emails.id ';
			$query_array['from'] = 'FROM emails ';
			$query_array['where'] = '';
			$query_array['join'] = ' JOIN (select distinct email_id from emails_email_addr_rel eear
						join email_addr_bean_rel eabr on (eabr.bean_id = "'.$focus->id.'" '.$query_join1.') and eabr.bean_module = "Accounts" and
						eabr.email_address_id = eear.email_address_id and eabr.deleted=0
						where eear.deleted=0 and eear.email_id not in
						(select eb.email_id from emails_beans eb where eb.bean_module = "Accounts" and (eb.bean_id = "'.$focus->id.'" '.$query_join2.'))
						) derivedemails on derivedemails.email_id = emails.id';
			$query_array['join_tables'] = array();


		// with parent_id & parent_type
		} else {
			$query_array['select'] = 'SELECT '.$param['subpanel_module'].'.id';
			$query_array['from'] = 'FROM '.$param['subpanel_module'];
			$query_array['where'] = 'WHERE '.$param['subpanel_module'].'.parent_type = "Accounts"
										AND '.$param['subpanel_module'].'.deleted = 0
										AND ('.$param['subpanel_module'].'.parent_id = "'.$focus->id.'" '.$query_where.')';
			$query_array['join'] = '';
			$query_array['join_tables'] = array();
		}


	return $query_array;
}

?>
