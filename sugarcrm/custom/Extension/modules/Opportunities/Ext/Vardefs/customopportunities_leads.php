<?php
/*
** @author: EDDY
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #14114:
** Description: keeping references to leadcontacts and leadaccounts instead of leads.  References
** is also kept in relationships
*/  
if (isset($dictionary['Opportunity']['fields']) && isset($dictionary['Opportunity']['fields']['leads'])) 
unset($dictionary['Opportunity']['fields']['leads']);

$dictionary['Opportunity']['fields']['leadaccounts'] =
  array (
  	'name' => 'leadaccounts',
	'type' => 'link',
    	'relationship' => 'opportunity_leadaccounts',
    	'source'=>'non-db',
	'vname'=>'LBL_LEADACCOUNTS',
  );
$dictionary['Opportunity']['fields']['leadcontacts'] =
  array (
  	'name' => 'leadcontacts',
	'type' => 'link',
    	'relationship' => 'opportunity_leadcontacts',
    	'source'=>'non-db',
	'vname'=>'LBL_LEADCONTACTS',
  );

if(isset($dictionary['Opportunity']['relationships']) && isset($dictionary['Opportunity']['relationships']['opportunity_leads'])) unset($dictionary['Opportunity']['relationships']['opportunity_leads']);
$dictionary['Opportunity']['relationships']['opportunity_leadaccounts'] =
	 array('lhs_module'=> 'Opportunities', 'lhs_table'=> 'opportunities', 'lhs_key' => 'id',
		'rhs_module'=> 'LeadAccounts', 'rhs_table'=> 'leadaccounts', 'rhs_key' => 'opportunity_id',
		'relationship_type'=>'one-to-many');
?>
