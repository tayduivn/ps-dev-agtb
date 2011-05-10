<?php
$dictionary['ext_rest_test'] = array(
  'comment' => 'A test connector',
  'fields' => array (
    'id' => array (
	    'name' => 'id',
	    'vname' => 'LBL_ID',
	    'hidden' => true,
	),  
    'firstname' => array (
	    'name' => 'firstname',
	    'vname' => 'LBL_FIRST_NAME',
    ),  
    'lastname'=> array(
	    'name' => 'lastname',
	    'vname' => 'LBL_LAST_NAME',	    
	    'input' => 'name.last',
	    'search' => true,
            'hover' => 'true',
    ),
    'website'=> array(
	    'name' => 'website',
	    'vname' => 'LBL_WEBSITE',	    
    ),
    'state'=> array(
	    'name' => 'state',
	    'vname' => 'LBL_STATE',
        'options' => 'states_dom',	
    ),  
  )
);
?>