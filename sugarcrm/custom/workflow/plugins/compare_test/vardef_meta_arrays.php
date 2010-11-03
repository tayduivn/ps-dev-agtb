<?php


//Add any custom vardef meta array elements to this page
//
$vardef_meta_array['compare_test_filter'] =  array(	
		'inclusion' =>	array(
		//end inclusion
		),			
		'exclusion' =>	array(	
			'type' => array('id'),
			'name' => array('parent_type', 'deleted'),
			'reportable' => array('false'),		
		//end exclusion
		),	
		'inc_override' => array(
			'type' => array('team_list'),	
		//end inc_override
		),	
		'ex_override' => array(
		//end ex_override
		)
	//end standard_display	
	);	


?>