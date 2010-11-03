<?php 
 //WARNING: The contents of this file are auto-generated


//BEGIN SUGAR INTERNAL CUSTOMIZATIONS - jgreen
$dictionary['Call']['fields']['leads'] = array (
		

			'name' => 'leads',
			'type' => 'link',
			'relationship' => 'lead_calls',
			'module'=>'Leads',
			'bean_name'=>'Lead',
			'source' => 'non-db',
			'vname' => 'LBL_LEADS',
		

  		);
  	
//END SUGAR INTERNAL CUSTOMIZATIONS - jgreen
			
		




// set up the M2 stuff for the Calls Module
// jwhitcraft 3.12.10
//unset($dictionary['Call']['fields']['leads']);

$dictionary['Call']['fields']['direction']['default'] = 'Outbound';

$dictionary['Call']['fields']['leadcontacts'] = array (
    'name' => 'leadcontacts',
    'type' => 'link',
    'relationship' => 'calls_leadcontacts',
    'source'=>'non-db',
    'vname'=>'LBL_LEADS',
    'comment' => 'Used to discover attendees',
);

$dictionary['Call']['fields']['leadcontact_related'] = array(
    'name' => 'leadcontact_related',
    'type' => 'link',
    'relationship' => 'leadcontact_calls',
    'source'=>'non-db',
    'vname'=>'LBL_LEADS',
    'comment' => 'find lead contact related to the call by parent_id',
);

// end jwhitcraft


// created: 2010-07-27 10:20:48
$dictionary["Call"]["fields"]["sales_seticket_activities_calls"] = array (
  'name' => 'sales_seticket_activities_calls',
  'type' => 'link',
  'relationship' => 'sales_seticket_activities_calls',
  'source' => 'non-db',
);


// created: 2010-07-27 14:43:42
$dictionary["Call"]["fields"]["orders_activities_calls"] = array (
  'name' => 'orders_activities_calls',
  'type' => 'link',
  'relationship' => 'orders_activities_calls',
  'source' => 'non-db',
);

?>