<?php
/*
 * Created on Aug 2, 2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
$module_name = '<module_name>';
$_object_name = '<_object_name>';
$viewdefs[$module_name]['DetailView'] = array(
    'templateMeta' => array('form' => array('buttons'=>array('EDIT', 'DUPLICATE', 'DELETE', 'FIND_DUPLICATES')),
                            'maxColumns' => '2', 
                            'widths' => array(
                                            array('label' => '10', 'field' => '30'), 
                                            array('label' => '10', 'field' => '30')
                                            ),
                           ),
    'panels' => array(
        array('name', 'phone_office'),
        array(array('name'=>'website', 'type'=>'link'), 'phone_fax'),
        array('ticker_symbol', array('name'=>'phone_alternate', 'label'=>'LBL_OTHER_PHONE')),
        array('', 'employees'),
        array('ownership', 'rating'),
        array('industry'),
        array($_object_name . '_type', 'annual_revenue'),
		array(
            array('name'=>'date_modified', 'label'=>'LBL_DATE_MODIFIED', 'customCode'=>'{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}'),
            //BEGIN SUGARCRM flav=pro ONLY
            'team_name',
            //END SUGARCRM flav=pro ONLY
        ),
		array(array('name'=>'assigned_user_name', 'label'=>'LBL_ASSIGNED_TO_NAME'),
              array('name'=>'date_entered', 'customCode'=>'{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}')),
		array (
		      array (
			  'name' => 'billing_address_street',
		      'label'=> 'LBL_BILLING_ADDRESS',
		      'type' => 'address',
		      'displayParams'=>array('key'=>'billing'),
		      ),
		array (
		      'name' => 'shipping_address_street',
		      'label'=> 'LBL_SHIPPING_ADDRESS',
		      'type' => 'address',
		      'displayParams'=>array('key'=>'shipping'),      
		      ),
		),

	    array('description'),
	    array('email1'),		      
     ),
    
    
);
?>
