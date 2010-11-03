<?php 
 //WARNING: The contents of this file are auto-generated


//BEGIN SUGAR INTERNAL CUSTOMIZATIONS - jgreen
$dictionary['Note']['fields']['leads'] = array (
		

			'name' => 'leads',
			'type' => 'link',
			'relationship' => 'lead_calls',
			'module'=>'Leads',
			'bean_name'=>'Lead',
			'source' => 'non-db',
			'vname' => 'LBL_LEADS',
		

  		);
  	
//END SUGAR INTERNAL CUSTOMIZATIONS - jgreen
			
		



  		



 // created: 2010-05-13 18:33:26
$dictionary['Note']['fields']['portal_flag']['default_value']='1';
$dictionary['Note']['fields']['portal_flag']['default']='1';
$dictionary['Note']['fields']['portal_flag']['required']=false;

 

// created: 2010-07-27 10:20:48
$dictionary["Note"]["fields"]["sales_seticket_activities_notes"] = array (
  'name' => 'sales_seticket_activities_notes',
  'type' => 'link',
  'relationship' => 'sales_seticket_activities_notes',
  'source' => 'non-db',
);


 // created: 2010-10-07 13:30:40
$dictionary['Note']['fields']['description']['calculated']=false;
$dictionary['Note']['fields']['description']['rows']='15';
$dictionary['Note']['fields']['description']['cols']='100';

 

// created: 2010-07-27 14:43:43
$dictionary["Note"]["fields"]["orders_activities_notes"] = array (
  'name' => 'orders_activities_notes',
  'type' => 'link',
  'relationship' => 'orders_activities_notes',
  'source' => 'non-db',
);

?>