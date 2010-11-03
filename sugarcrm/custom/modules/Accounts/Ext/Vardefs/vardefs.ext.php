<?php 
 //WARNING: The contents of this file are auto-generated



//$dictionary['Account']['fields']['account_type']['required'] = true;
$dictionary['Account']['fields']['account_type']['audited'] = true;
$dictionary['Account']['fields']['account_type']['type'] = 'enum';
$dictionary['Account']['fields']['industry']['len'] = '50';
$dictionary['Account']['fields']['industry']['type'] = 'enum';
//$dictionary['Account']['fields']['industry']['required'] = true;
//$dictionary['Account']['fields']['employees']['required'] = true;
//$dictionary['Account']['fields']['annual_revenue']['required'] = true;


// created: 2009-02-13 17:29:10
$dictionary["Account"]["fields"]["opportunities_accounts"] = array (
  'name' => 'opportunities_accounts',
  'type' => 'link',
  'relationship' => 'opportunities_accounts',
  'source' => 'non-db',
);




/* SADEK 2008-04-03 - REMOVED SUBPANEL SINCE WE NO LONGER USE THIS MODULE
$dictionary['Account']['fields']['download_keys'] =   array (
        'name' => 'download_keys',
    'type' => 'link',
    'relationship' => 'download_keys_accounts',
    'module'=>'DownloadKeys',
    'bean_name'=>'DownloadKey',
    'source'=>'non-db',
                'vname'=>'LBL_DOWNLOAD_KEYS',
);
*/
$dictionary['Account']['fields']['sugar_installations'] =   array (
        'name' => 'sugar_installations',
    'type' => 'link',
    'relationship' => 'sugar_installations_accounts',
    'module'=>'SugarInstallations',
    'bean_name'=>'SugarInstallation',
    'source'=>'non-db',
        'vname'=>'LBL_SUGAR_INSTALLATIONS',
);

/* SADEK 2008-04-03 - REMOVED SUBPANEL SINCE WE NO LONGER USE THIS MODULE
$dictionary['Account']['relationships']['download_keys_accounts'] = array(
                        'lhs_module'=> 'Accounts',
                        'lhs_table'=> 'accounts',
                        'lhs_key' => 'id',
                        'rhs_module'=> 'DownloadKeys',
                        'rhs_table'=> 'download_keys',
                        'rhs_key' => 'account_id',
                        'relationship_type'=>'one-to-many'
);
*/
$dictionary['Account']['relationships']['sugar_installations_accounts'] = array(
                        'lhs_module'=> 'Accounts',
                        'lhs_table'=> 'accounts',
                        'lhs_key' => 'id',
                        'rhs_module'=> 'SugarInstallations',
                        'rhs_table'=> 'sugar_installations',
                        'rhs_key' => 'account_id',
                        'relationship_type'=>'one-to-many'
);






$dictionary['Account']['fields']['subscriptions'] =   array (
    'name' => 'subscriptions',
    'type' => 'link',
    'relationship' => 'accounts_subscriptions',
    'module'=>'subscriptions',
    'bean_name'=>'Subscriptions',
    'source'=>'non-db',
    'vname'=>'LBL_SUBSCRIPTIONS',
);

$dictionary['Account']['relationships']['accounts_subscriptions'] = array(
                        'lhs_module'=> 'Accounts',
                        'lhs_table'=> 'accounts',
                        'lhs_key' => 'id',
                        'rhs_module'=> 'Subscriptions',
                        'rhs_table'=> 'subscriptions',
                        'rhs_key' => 'account_id',
                        'relationship_type'=>'one-to-many'
);




$dictionary['Account']['fields']['itrequests'] =   array (
    'name' => 'itrequests',
    'type' => 'link',
    'relationship' => 'itrequests_accounts',
    'module'=>'ITRequests',
    'bean_name'=>'ITRequest',
    'source'=>'non-db',
    'vname'=>'LBL_ITREQUESTS',
);






// created: 2010-07-21 13:42:19
$dictionary["Account"]["fields"]["cr_customer_reference_accounts"] = array (
  'name' => 'cr_customer_reference_accounts',
  'type' => 'link',
  'relationship' => 'cr_customer_reference_accounts',
  'source' => 'non-db',
  'vname' => 'LBL_CR_CUSTOMER_REFERENCE_ACCOUNTS_FROM_CR_CUSTOMER_REFERENCE_TITLE',
);
$dictionary["Account"]["fields"]["cr_customer_reference_accounts_name"] = array (
  'name' => 'cr_customer_reference_accounts_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_CR_CUSTOMER_REFERENCE_ACCOUNTS_FROM_CR_CUSTOMER_REFERENCE_TITLE',
  'save' => true,
  'id_name' => 'cr_custome30f7ference_ida',
  'link' => 'cr_customer_reference_accounts',
  'table' => 'cr_customer_reference',
  'module' => 'CR_Customer_Reference',
  'rname' => 'name',
);
$dictionary["Account"]["fields"]["cr_custome30f7ference_ida"] = array (
  'name' => 'cr_custome30f7ference_ida',
  'type' => 'link',
  'relationship' => 'cr_customer_reference_accounts',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'left',
  'vname' => 'LBL_CR_CUSTOMER_REFERENCE_ACCOUNTS_FROM_CR_CUSTOMER_REFERENCE_TITLE',
);


// created: 2010-07-27 14:40:36
$dictionary["Account"]["fields"]["accounts_orders"] = array (
  'name' => 'accounts_orders',
  'type' => 'link',
  'relationship' => 'accounts_orders',
  'source' => 'non-db',
  'side' => 'right',
  'vname' => 'LBL_ACCOUNTS_ORDERS_FROM_ORDERS_TITLE',
);


// created: 2010-07-27 14:46:32
$dictionary["Account"]["fields"]["discountcodes_accounts"] = array (
  'name' => 'discountcodes_accounts',
  'type' => 'link',
  'relationship' => 'discountcodes_accounts',
  'source' => 'non-db',
  'vname' => 'LBL_DISCOUNTCODES_ACCOUNTS_FROM_DISCOUNTCODES_TITLE',
);
$dictionary["Account"]["fields"]["discountcodes_accounts_name"] = array (
  'name' => 'discountcodes_accounts_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_DISCOUNTCODES_ACCOUNTS_FROM_DISCOUNTCODES_TITLE',
  'save' => true,
  'id_name' => 'discountco4170ntcodes_ida',
  'link' => 'discountcodes_accounts',
  'table' => 'discountcodes',
  'module' => 'DiscountCodes',
  'rname' => 'name',
);
$dictionary["Account"]["fields"]["discountco4170ntcodes_ida"] = array (
  'name' => 'discountco4170ntcodes_ida',
  'type' => 'link',
  'relationship' => 'discountcodes_accounts',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'right',
  'vname' => 'LBL_DISCOUNTCODES_ACCOUNTS_FROM_ACCOUNTS_TITLE',
);


 // created: 2010-10-19 07:24:30
$dictionary['Account']['fields']['customer_msa_not_required_c']['enforced']='false';

 
?>