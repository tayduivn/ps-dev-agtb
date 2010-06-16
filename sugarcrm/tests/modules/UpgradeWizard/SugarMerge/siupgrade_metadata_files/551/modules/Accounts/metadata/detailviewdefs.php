<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
$viewdefs['Accounts']['DetailView'] = array(
    'templateMeta' => array('form' => array('buttons'=>array('EDIT', 'DUPLICATE', 'DELETE', 'FIND_DUPLICATES')),
                            'maxColumns' => '2', 
                            'widths' => array(
                                            array('label' => '10', 'field' => '30'), 
                                            array('label' => '10', 'field' => '30')
                                            ),
                            'includes'=> array(
                                            array('file'=>'modules/Accounts/Account.js'),
                                         ),                                            
                           ),
    'panels' => array(
        'default'=> array(
	        array('name', 'phone_office'),
	        array(array('name'=>'website', 'type'=>'link','label'=>'LBL_WEBSITE',			      'displayParams'=>array('link_target'=>'_blank')), 'phone_fax'),
	        array('ticker_symbol', array('name'=>'phone_alternate', 'label'=>'LBL_OTHER_PHONE')),
	        array('parent_name', 'employees'),
	        array('ownership', 'rating'),
	        array('industry', 'sic_code'),
	        array('account_type', 'annual_revenue'),
			array(
				//BEGIN SUGARCRM flav=pro ONLY
				'team_name', 
				//END SUGARCRM flav=pro ONLY
			      array('name'=>'date_modified', 'label'=>'LBL_DATE_MODIFIED', 'customCode'=>'{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}')),
			array(array('name'=>'assigned_user_name', 'label'=>'LBL_ASSIGNED_TO'),
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
            array('campaign_name'),
		    array('email1'),
	    ),	      
     ),
    
    
);
?>