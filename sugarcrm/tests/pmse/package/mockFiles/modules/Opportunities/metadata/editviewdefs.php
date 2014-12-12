<?php
$viewdefs['Opportunities']['EditView'] = array(
    'templateMeta' => array('maxColumns' => '2', 
                            'widths' => array(
                                            array('label' => '10', 'field' => '30'), 
                                            array('label' => '10', 'field' => '30')
                                            ),
    'javascript' => '{$PROBABILITY_SCRIPT}',
),
 'panels' =>array (
  'default' => 
  array (
    
    array (
      array('name'=>'name'),
      'account_name',
    ),
    array(
    	array('name'=>'currency_id','label'=>'LBL_CURRENCY'),
    	array('name'=>'date_closed'),
    ),
    array (
      array( 'name'=>'amount'),
      'opportunity_type',
    ),
    array (
      'sales_stage',
      'lead_source',
    ),
    array (      
		'probability',
      	'campaign_name',
    ),
    array (
      	'next_step',
    ),
    array (
      'description',
    ),
  ),
  
  'LBL_PANEL_ASSIGNMENT' => array(
    array(
	    'assigned_user_name',
    ),
  ),
)


);
?>