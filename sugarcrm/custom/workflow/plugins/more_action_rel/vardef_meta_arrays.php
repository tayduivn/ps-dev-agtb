<?php

//Add any custom vardef meta array elements to this page
//
$vardef_meta_array['action_filter'] = array(
		'inclusion' =>	array(
		//end inclusion
		),	
		'exclusion' =>	array(	
			'type' => array('id', 'datetime', 'time'),
			'custom_type' => array('id', 'datetime', 'time'),
			'reportable' => array('false'),
			'source' => array('non-db'),
			'name' => array('created_by', 'parent_type', 'deleted', 'assigned_user_name', 'amount_backup', 'amount_usdollar', 'deleted' ,'filename', 'file_mime_type', 'file_url'),
		//end exclusion
		),
		
		'inc_override' => array(
			'type' => array('team_list'),
			'name' => array('assigned_user_id', 'time_start', 'date_start'),
		//end inc_override
		),
		'ex_override' => array(
			'name' => array('team_name', 'account_name'),
		//end ex_override
		)
	
	//end action_filter
	);
	


?>
