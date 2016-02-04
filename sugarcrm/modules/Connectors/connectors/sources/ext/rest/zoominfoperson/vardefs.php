<?php

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
$dictionary['ext_rest_zoominfoperson'] = array(
  'comment' => 'vardefs for ZoomInfo Person connector',
  'fields' => array (
    'id' => array (
	    'name' => 'id',
	    'vname' => 'LBL_ID',
	    'hidden' => true,
	),  
	'firstname' => array (
	    'name' => 'firstname',
	    'vname' => 'LBL_FIRST_NAME',
	    'input' => 'firstName',
	    'search' => true,
    ),  
    'lastname'=> array(
	    'name' => 'lastname',
	    'vname' => 'LBL_LAST_NAME',	    
	    'input' => 'lastName',
	    'search' => true,
    ),
    'email' => array(
	    'name' => 'email',
	    'vname' => 'LBL_EMAIL',	    
	    'input' => 'EmailAddress',
	    'search' => true,    
    ),
    'imageurl' => array(
	    'name' => 'imageurl',
	    'vname' => 'LBL_IMAGE_URL',
    ),
    'companyname' => array(
	    'name' => 'companyname',
	    'vname' => 'LBL_COMPANY_NAME',
	    'input' => 'companyName',
        'search' => true, 
    ),
    'zoompersonurl' => array(
		'name' => 'zoompersonurl',
    	'vname' => 'LBL_ZOOMPERSON_URL',    
    ),
    'directphone' => array(
		'name' => 'directphone',
    	'vname' => 'LBL_DIRECT_PHONE',    
    ),
    'companyphone' => array(
		'name' => 'companyphone',
    	'vname' => 'LBL_COMPANY_PHONE',    
    ),            
    'fax' => array(
		'name' => 'fax',
    	'vname' => 'LBL_FAX',    
    ), 
    'jobtitle' => array(
    	'name'=>'jobtitle',
    	'vname' => 'LBL_CURRENT_JOB_TITLE',
    ),
    'current_job_start_date' => array(
    	'name'=>'current_job_start_date',
    	'vname' => 'LBL_CURRENT_JOB_START_DATE',
    ),
    'companyname' => array(
    	'name'=>'companyname',
    	'vname' => 'LBL_CURRENT_JOB_COMPANY_NAME',
        'search'=>true,
    ),
    'street' => array(
    	'name'=>'street',
    	'vname' => 'LBL_CURRENT_JOB_COMPANY_STREET',
    ),  
    'city' => array(
    	'name'=>'city',
    	'vname' => 'LBL_CURRENT_JOB_COMPANY_CITY',
    ),  
    'state' => array(
    	'name'=>'state',
    	'vname' => 'LBL_CURRENT_JOB_COMPANY_STATE',
    ),  
    'zip' => array(
    	'name'=>'current_job_company_zip',
    	'vname' => 'LBL_CURRENT_JOB_COMPANY_ZIP',
    ),  
    'countrycode' => array(
    	'name'=>'countrycode',
    	'vname' => 'LBL_CURRENT_JOB_COMPANY_COUNTRY_CODE',
    ),  
    'industry' => array(
    	'name'=>'industry',
    	'vname' => 'LBL_CURRENT_INDUSTRY',
    ), 
	'biography' => array(
		'name'=>'biography',
    	'vname' => 'LBL_BIOGRAPHY',    
    ),    
    'school' => array(
    	'name' => 'school',
    	'vname' => 'LBL_EDUCATION_SCHOOL',
        'search' => true,
    ),
    'affiliation_title' => array(
        'name' => 'affiliation_title',
        'vname' => 'LBL_AFFILIATION_TITLE',
        'input' => 'JobTitle',
    ),
    'affiliation_company_name' => array(
        'name' => 'affiliation_company_name',
        'vname' => 'LBL_AFFILIATION_COMPANY_NAME',        
    ),
    'affiliation_company_phone' => array(
        'name' => 'affiliation_company_phone',
        'vname' => 'LBL_AFFILIATION_COMPANY_PHONE',        
    ),    
    'affiliation_company_website' => array(
        'name' => 'affiliation_company_website',
        'vname' => 'LBL_AFFILIATION_COMPANY_WEBSITE',      
    )                      
   )
);
?>
