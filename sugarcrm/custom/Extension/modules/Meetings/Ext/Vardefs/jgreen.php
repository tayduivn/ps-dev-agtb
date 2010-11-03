<?PHP
//BEGIN SUGAR INTERNAL CUSTOMIZATIONS - jgreen
$dictionary['Meeting']['fields']['leads'] = array (
		

			'name' => 'leads',
			'type' => 'link',
			'relationship' => 'lead_meetings',
			'module'=>'Leads',
			'bean_name'=>'Lead',
			'source' => 'non-db',
			'vname' => 'LBL_LEADS',
		

  		);
  	
//END SUGAR INTERNAL CUSTOMIZATIONS - jgreen
			
/*
** @author: EDDY
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #14114:
** Description: keeping references to leadcontacts instead of leads
*/
$dictionary['Meeting']['fields']['leadcontacts'] =
  array (
    'name' => 'leadcontacts',
    'type' => 'link',
    'relationship' => 'meetings_leadcontacts',
    'source'=>'non-db',
        'vname'=>'LBL_LEADS',
  );
		

?>
