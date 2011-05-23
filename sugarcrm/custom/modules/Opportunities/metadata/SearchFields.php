<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/**
 *  Custom query when searching for "my items" only:
 *	- Opportunities assinged to the current user based upon opportunities_users relationship
 *  - Opportunities related to Accounts for which the current user is assigned to (not accounts_users)
 *
 * ==> include/SearchForm/SearchForm2.php is non-upgrade safe changed to support 
 * 	   the "my_items_custom_query" parameter
 * 
 */

$searchFields['Opportunities']['current_user_only'] = array(
	'query_type'=>'format',
	'operator' => 'subquery',
	'subquery' => 'SELECT opus.opportunity_id
					FROM opportunities_users opus
					INNER JOIN opportunities opp
						ON opp.id = opus.opportunity_id
						AND opp.deleted = 0
					WHERE opus.user_id =\'{1}\'
						AND opus.deleted = 0								
	
					UNION	
						
					SELECT acop.opportunity_id
					FROM accounts acc
					INNER JOIN accounts_opportunities acop
						ON acop.account_id = acc.id
						AND acop.deleted = 0
					INNER JOIN opportunities opp
						ON opp.id = acop.opportunity_id
						AND opp.deleted = 0
					WHERE acc.assigned_user_id = \'{1}\'
						AND acc.deleted = 0
					',				
	'db_field' => array('id'),
	'my_items' => true,
	'value' => false,
	'my_items_custom_query' => true,
);

?>