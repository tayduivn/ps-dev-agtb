<?php
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
$viewdefs['Accounts']['EditView'] = array(
    'templateMeta' => array(
                            'form' => array('buttons'=>array('SAVE', 'CANCEL')),
                            'maxColumns' => '2', 
                            'widths' => array(
                                            array('label' => '10', 'field' => '30'),
                                            array('label' => '10', 'field' => '30'),
                                            ),
                            'includes'=> array(
                                            array('file'=>'modules/Accounts/Account.js'),
                                         ),
                           ),
                           
    'panels' => array(
	   'lbl_account_information'=>array(
		        array(
		        	array(
		        			'name'=>'name',
		        			 'label'=>'LBL_NAME',
		        			  'displayParams'=>array('required'=>true)
		        	), 
		        	array(
		        		'name'=>'phone_office',
		        		 'label'=>'LBL_PHONE_OFFICE'
		        	)
		       
				),
		        array(
		        	array(
		        		'name'=>'website', 
		        		'type'=>'link',
		        		'label'=>'LBL_WEBSITE'
		        	), 
		        	array(
		        		'name'=>'phone_fax', 
		        		 'label'=>'LBL_PHONE_FAX'
					)
		        ),
		        array(
		        	array(
		        		'name'=>'ticker_symbol',
		        		'label'=>'LBL_TICKER_SYMBOL'
		        	 ),
		        	 array(
		        	 	'name'=>'phone_alternate',
		        	 	 'label'=>'LBL_OTHER_PHONE'
		        	 	 )
		        ),
	        array(
	        	array('name'=>'parent_name','label' => 'LBL_MEMBER_OF'),
	        	array('name'=>'employees','label' => 'LBL_EMPLOYEES' )
	        ),	        
	         array(
	        	array('name'=>'ownership','label' => 'LBL_OWNERSHIP'),
	        	array('name'=>'rating','label' => 'LBL_RATING' )
	        ),
	        array(
	        	array('name'=>'industry','label' => 'LBL_INDUSTRY'),
	        	array('name'=>'sic_code','label' => 'LBL_SIC_CODE' )
	        ),
	        array(
	        	array('name'=>'account_type'),
	        	array('name'=>'annual_revenue','label' => 'LBL_ANNUAL_REVENUE' )
	        ),
            array(
                array('name'=>'campaign_name')
            ),
		        //BEGIN SUGARCRM flav=pro ONLY
		        array(array('name'=>'team_name', 'displayParams'=>array('display'=>true)), ''),
		        //END SUGARCRM flav=pro ONLY
                array(
                	array('name'=>'assigned_user_name','label' =>'LBL_ASSIGNED_TO')
                )
	   ),
	   'lbl_address_information'=>array(
				array (
				      array (
					  'name' => 'billing_address_street',
				      'hideLabel'=> true,
				      'type' => 'address',
				      'displayParams'=>array('key'=>'billing', 'rows'=>2, 'cols'=>30, 'maxlength'=>150),
				      ),
				array (
				      'name' => 'shipping_address_street',
				      'hideLabel' => true,
				      'type' => 'address',
				      'displayParams'=>array('key'=>'shipping', 'copy'=>'billing', 'rows'=>2, 'cols'=>30, 'maxlength'=>150),      
				      ),
				),
	   ),
	   
  	   'lbl_email_addresses'=>array(
  				array('email1')
  	   ),
  	   
	   'lbl_description_information' =>array(
		        array(array('name'=>'description', 'displayParams'=>array('cols'=>80, 'rows'=>6),'label' => 'LBL_DESCRIPTION')),
	   ),
	    
    )
);
?>
