<?php

$module_name = 'pmse_Emails_Templates';
$viewdefs[$module_name]['QuickCreate'] = array(
    'templateMeta' => array('maxColumns' => '2', 
                            'widths' => array(
                                            array('label' => '10', 'field' => '30'), 
                                            array('label' => '10', 'field' => '30')
                                            ),                                                                                                                                    
                                            ),
                                            
                                            
 'panels' =>array (
  'default' => 
  array (
    
    array (
      'name',
      'assigned_user_name',
    ),
    array (
	  'description',
      array('name'=>'team_name', 'displayParams'=>array('display'=>true)),
    ),
  ),
                                                    
),
                        
);
?>