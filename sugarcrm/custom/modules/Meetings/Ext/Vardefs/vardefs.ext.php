<?php 
 //WARNING: The contents of this file are auto-generated


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
		




// created: 2010-07-27 10:20:48
$dictionary["Meeting"]["fields"]["sales_seticket_activities_meetings"] = array (
  'name' => 'sales_seticket_activities_meetings',
  'type' => 'link',
  'relationship' => 'sales_seticket_activities_meetings',
  'source' => 'non-db',
);


// created: 2010-07-27 14:43:42
$dictionary["Meeting"]["fields"]["orders_activities_meetings"] = array (
  'name' => 'orders_activities_meetings',
  'type' => 'link',
  'relationship' => 'orders_activities_meetings',
  'source' => 'non-db',
);

?>