<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/**
 *  Custom query when searching for "my items" only:
 *	- Accounts the current user is assigned to (existing behavior)
 *	- Accounts that you are part of the account team (accounts_users)
 *
 * ==> include/SearchForm/SearchForm2.php is non-upgrade safe changed to support 
 * 	   the "my_items_custom_query" parameter
 * 
 */

$searchFields['Accounts']['current_user_only'] = array(
	'query_type'=>'format',
	'operator' => 'subquery',
	'subquery' => 'SELECT accounts.id 
						FROM accounts 
					WHERE accounts.deleted=0
						AND accounts.assigned_user_id = \'{1}\'

					UNION

					SELECT acu.account_id
					FROM accounts_users acu
					INNER JOIN accounts acc
						ON acc.id = acu.account_id
						AND acc.deleted = 0
					WHERE acu.user_id = \'{1}\'
					AND acu.deleted = 0',				
	'db_field' => array('id'),
	'my_items' => true,
	'value' => false,
	'my_items_custom_query' => true,
);

?>