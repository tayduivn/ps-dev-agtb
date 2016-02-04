<?php

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
$mapping = array (
  'beans' => 
  array (
    'Leads' => 
    array (
      'companyname' => 'account_name', 
      'street' => 'primary_address_street',   
      'city' => 'primary_address_city',
      'state' => 'primary_address_state',
      'zip' => 'primary_address_postalcode',
      'countrycode' => 'primarty_address_country',
      'companydescription' => 'description',
      'phone' => 'phone_work',
    ),
    'Accounts' => 
    array (
      'companyname' => 'name',
      'street' => 'billing_address_street',    
      'city' => 'billing_address_city',
      'state' => 'billing_address_state',
      'zip' => 'billing_address_postalcode',
      'countrycode' => 'billing_address_country',
      'industry' => 'industry',      
      'website' => 'website',      
      'companydescription' => 'description',
      'revenue' => 'annual_revenue',
      'phone' => 'phone_office',
      'employees' => 'employees',    
      'id' => 'id',
    ),    
    'Contacts' => 
    array (
      'companyname' => 'account_name',
      'street' => 'primary_address_street',    
      'city' => 'primary_address_city',
      'state' => 'primary_address_state',
      'zip' => 'primary_address_postalcode',
      'countrycode' => 'primary_address_country',
      'companydescription' => 'description',
    ),          
  ),
);
?>
