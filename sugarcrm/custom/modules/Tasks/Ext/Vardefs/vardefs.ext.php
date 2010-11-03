<?php 
 //WARNING: The contents of this file are auto-generated


$dictionary['Task']['audited'] = true;
//BEGIN SUGAR INTERNAL CUSTOMIZATIONS - jgreen
$dictionary['Task']['fields']['leads'] = array (
		

			'name' => 'leads',
			'type' => 'link',
			'relationship' => 'lead_calls',
			'module'=>'Leads',
			'bean_name'=>'Lead',
			'source' => 'non-db',
			'vname' => 'LBL_LEADS',
		

  		);
  	
//END SUGAR INTERNAL CUSTOMIZATIONS - jgreen

$dictionary['Task']['fields']['status']['audited'] = true;
$dictionary['Task']['fields']['status']['type'] = 'enum';
$dictionary['Task']['fields']['priority']['default'] = 'Medium';




//DEE CUSTOMIZATION 06.01.2009 - ITREQUEST 7136
$dictionary['Task']['fields']['assigned_user_name'] =
    array (
      'name' => 'assigned_user_name',
      'link' => 'assigned_user_link',
      'vname' => 'LBL_ASSIGNED_TO_NAME',
      'rname' => 'user_name',
      'type' => 'relate',
      'reportable' => false,
      'source' => 'non-db',
      'table' => 'users',
      'id_name' => 'assigned_user_id',
      'module' => 'Users',
      'duplicate_merge' => 'disabled',
      'required' => true,
    );

//END DEE CUSTOMIZATION



// created: 2010-07-02 19:32:01
$dictionary["Task"]["fields"]["ps_timesheets_tasks"] = array (
  'name' => 'ps_timesheets_tasks',
  'type' => 'link',
  'relationship' => 'ps_timesheets_tasks',
  'source' => 'non-db',
  'side' => 'right',
  'vname' => 'LBL_PS_TIMESHEETS_TASKS_FROM_PS_TIMESHEETS_TITLE',
);


 // created: 2010-08-23 15:56:52
$dictionary['Task']['fields']['date_start']['display_default']='now&12:00am';

 

 // created: 2010-08-23 15:57:17
$dictionary['Task']['fields']['date_due']['display_default']='now&01:00am';

 

// created: 2010-07-27 10:20:48
$dictionary["Task"]["fields"]["sales_seticket_activities_tasks"] = array (
  'name' => 'sales_seticket_activities_tasks',
  'type' => 'link',
  'relationship' => 'sales_seticket_activities_tasks',
  'source' => 'non-db',
);


// created: 2010-07-27 14:43:43
$dictionary["Task"]["fields"]["orders_activities_tasks"] = array (
  'name' => 'orders_activities_tasks',
  'type' => 'link',
  'relationship' => 'orders_activities_tasks',
  'source' => 'non-db',
);

?>