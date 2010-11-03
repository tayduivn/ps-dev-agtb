<?PHP
//BEGIN SUGAR INTERNAL CUSTOMIZATIONS - jgreen
$dictionary['Lead']['fields']['members'] = array (
		

			'name' => 'members',
			'type' => 'link',
			'relationship' => 'member_leads',
			'module'=>'Leads',
			'bean_name'=>'Lead',
			'source' => 'non-db',
			'vname' => 'LBL_MEMBERS',
		

  		);

//$dictionary['Lead']['fields']['status']['massupdate'] = true;
  		
  $dictionary['Lead']['fields']['member_of'] = array (

			'name' => 'member_of',
			'type' => 'link',
			'relationship' => 'member_leads',
			'module'=>'Leads',
			'bean_name'=>'Lead',
				'link_type'=>'one',
			'source' => 'non-db',
			'vname' => 'LBL_MEMBER_OF',
			'side'=>'right',
		
	
  		);		

	$dictionary['Lead']['fields']['parent_lead_id'] = array (
    	'name' => 'parent_lead_id',
    	'vname' => 'LBL_PARENT_LEAD_NAME',
    	'type' => 'id',
    	'required'=>false,
    	'reportable'=>false,
    	'audited'=>true,    
 	 );

   $dictionary['Lead']['fields']['last_response_date'] = array (
    'name' => 'last_response_date',
    'vname' => 'LBL_LAST_RESPONSE_DATE',
    'type' => 'datetime',
    'required' => 'true',
	'massupdate' => false,
  );

  $dictionary['Lead']['fields']['related_leads'] = array( 

  	'name' => 'related_leads',
    'type' => 'link',
    'relationship' => 'leads_leads',
    'source'=>'non-db',
		'vname'=>'LBL_RELATED_LEAD',
  );


$dictionary['Lead']['relationships']['member_leads'] = array (
				'lhs_module'=> 'Leads',
				'lhs_table'=> 'leads',
				'lhs_key' => 'id',
				'rhs_module'=> 'Leads',
				'rhs_table'=> 'leads',
				'rhs_key' => 'parent_lead_id',	
				'relationship_type'=>'one-to-many'
			);



   $dictionary['Lead']['fields']['screener_assign_date'] = array (
    'name' => 'screener_assign_date',
    'vname' => 'LBL_SCREENER_ASSIGN_DATE',
    'type' => 'datetime',
    'required' => 'true',
	'massupdate' => false,
  );
     $dictionary['Lead']['fields']['scrub_complete_date'] = array (
    'name' => 'scrub_complete_date',
    'vname' => 'LBL_SCRUB_COMPLETE_DATE',
    'type' => 'datetime',
    'required' => 'true',
	'massupdate' => false,
  );
     $dictionary['Lead']['fields']['group_assign_date'] = array (
    'name' => 'group_assign_date',
    'vname' => 'LBL_GROUP_ASSIGN_DATE',
    'type' => 'datetime',
    'required' => 'true',
	'massupdate' => false,
  );
     $dictionary['Lead']['fields']['rep_assign_date'] = array (
    'name' => 'rep_assign_date',
    'vname' => 'LBL_REP_ASSIGN_DATE',
    'type' => 'datetime',
    'required' => 'true',
	'massupdate' => false,
  );
     $dictionary['Lead']['fields']['initial_contact_date'] = array (
    'name' => 'initial_contact_date',
    'vname' => 'LBL_INITIAL_CONTACT_DATE',
    'type' => 'datetime',
    'required' => 'true',
	'massupdate' => false,
  );
     $dictionary['Lead']['fields']['last_activity_date'] = array (
    'name' => 'last_activity_date',
    'vname' => 'LBL_LAST_ACTIVITY_DATE',
    'type' => 'datetime',
    'required' => 'true',
	'massupdate' => false,
  );
     $dictionary['Lead']['fields']['conversion_date'] = array (
    'name' => 'conversion_date',
    'vname' => 'LBL_CONVERSION_DATE',
    'type' => 'datetime',
    'required' => 'true',
	'massupdate' => false,
  );
     $dictionary['Lead']['fields']['dead_date'] = array (
    'name' => 'dead_date',
    'vname' => 'LBL_DEAD_DATE',
    'type' => 'datetime',
    'required' => 'true',
	'massupdate' => false,
  );      
     $dictionary['Lead']['fields']['re_assignment_date'] = array (
    'name' => 're_assignment_date',
    'vname' => 'LBL_RE_ASSIGNMENT_DATE',
    'type' => 'datetime',
    'required' => 'true',
	'massupdate' => false,
  );  

//END SUGAR INTERNAL CUSTOMIZATIONS - jgreen
			
		

?>
