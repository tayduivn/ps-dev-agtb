<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
$viewdefs['Cases']['EditView'] = array(
    'templateMeta' => array('maxColumns' => '2', 
                            'widths' => array(
                                            array('label' => '10', 'field' => '30'), 
                                            array('label' => '10', 'field' => '30')
                                            ),
                           ),
    'panels' => array (
                         
  'lbl_case_information' => 
  array(
	  array (
	    array('name'=>'case_number', 'type'=>'readonly') ,
	  ),
	  
	  array (
	    'priority',
	  ),
	  
	  array (
	    'status',
	    'account_name',
	  ),
	  
	  array (
	      'type',
	  ),
	  array (
	    array (
	      'name' => 'name',
	      'displayParams' => array ('size'=>75)
	    ),
	  ),
	  
	  array (
	    
	    array (
	      'name' => 'description',
	      'nl2br' => true,
	    ),
	  ),
	  
	  array (
	    
	    array (
	      'name' => 'resolution',
	      'nl2br' => true,
	    ),
	  ),
	
	  //BEGIN SUGARCRM flav=ent ONLY
	  array(
		  array('name'=>'portal_viewable',
		        'customLabel'=>'{if ($PORTAL_ENABLED)}{sugar_translate label="LBL_SHOW_IN_PORTAL" module="Cases"}{/if}',
		        'customCode'=>' {if ($PORTAL_ENABLED)}
								{if $fields.portal_viewable.value == "1"}
								{assign var="checked" value="CHECKED"}
								{else}
								{assign var="checked" value=""}
								{/if}
								<input type="hidden" name="{$fields.portal_viewable.name}" value="0"> 
								<input type="checkbox" name="{$fields.portal_viewable.name}" value="1" tabindex="1" {$checked}>
		        		        {/if}',
		  ), 
	  )  
	  //END SUGARCRM flav=ent ONLY
	),
	
	'LBL_PANEL_ASSIGNMENT' => 
	array(
	   array (
		    'assigned_user_name',	   
		    //BEGIN SUGARCRM flav=pro ONLY
		    array('name'=>'team_name', 'displayParams'=>array('required'=>true)),
		    //END SUGARCRM flav=pro ONLY
	   ),
	),
),

  
);
?>