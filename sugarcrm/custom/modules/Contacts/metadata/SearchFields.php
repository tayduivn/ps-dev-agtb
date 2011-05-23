<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/**
 *  Custom query when searching for "my items" only:
 *	- Contacts the current user is assigned to (existing behavior)
 *	- Contacts that are associated to any ACCOUNT the current user is assigned to
 *	- Contacts that are associated to any OPPORTUNITY the current user is assigned to 
 *
 * ==> include/SearchForm/SearchForm2.php is non-upgrade safe changed to support 
 * 	   the "my_items_custom_query" parameter
 * 
 */

$searchFields['Contacts']['current_user_only'] = array(
	'query_type'=>'format',
	'operator' => 'subquery',
	'subquery' => 'SELECT contacts.id 
						FROM contacts 
					WHERE contacts.deleted=0
						AND contacts.assigned_user_id = \'{1}\'

					UNION

					SELECT acco.contact_id
						FROM accounts_users acus
					INNER JOIN accounts_contacts acco
						ON acus.account_id = acco.account_id
						AND acco.deleted = 0
					INNER JOIN accounts acc
						ON acc.id = acco.account_id
						AND acc.deleted = 0
					WHERE acus.user_id = \'{1}\'
						AND acus.deleted = 0	

					UNION
						
					SELECT opco.contact_id
						FROM opportunities_users opus
					INNER JOIN opportunities_contacts opco
						ON opus.opportunity_id = opco.opportunity_id
						AND opco.deleted = 0
					INNER JOIN opportunities opp
						ON opp.id = opco.opportunity_id
						AND opp.deleted = 0
					WHERE opus.user_id = \'{1}\'
						AND opus.deleted = 0',				
	'db_field' => array('id'),
	'my_items' => true,
	'value' => false,
	'my_items_custom_query' => true,
);

$searchFields['Contacts']['search_name'] = array(
	'query_type'=>'format',
	'operator' => 'subquery',
	'subquery' => '
					SELECT contacts.id 
						FROM contacts 
					WHERE contacts.deleted=0
						AND contacts.first_name LIKE \'{0}\'

					UNION
						
					SELECT contacts.id 
						FROM contacts 
					WHERE contacts.deleted=0
						AND contacts.last_name LIKE \'{0}\'

					UNION
					
					SELECT contacts.id 
						FROM contacts 
					WHERE contacts.deleted=0
						AND concat(contacts.first_name, \' \', contacts.last_name) LIKE \'{0}\'

					UNION
					
					SELECT contacts.id 
						FROM contacts INNER JOIN contacts_cstm on contacts.id = contacts_cstm.id_c
					WHERE contacts.deleted=0
						AND contacts_cstm.alt_lang_first_c LIKE \'{0}\'
					
					UNION
					
					SELECT contacts.id 
						FROM contacts INNER JOIN contacts_cstm on contacts.id = contacts_cstm.id_c
					WHERE contacts.deleted=0
						AND contacts_cstm.alt_lang_last_c LIKE \'{0}\'
					
					UNION
					
					SELECT contacts.id 
						FROM contacts INNER JOIN contacts_cstm on contacts.id = contacts_cstm.id_c
					WHERE contacts.deleted=0
						AND concat(contacts_cstm.alt_lang_last_c, \' \', contacts_cstm.alt_lang_last_c) LIKE \'{0}\'
					',				
	'db_field' => array('id'),
);

?>
