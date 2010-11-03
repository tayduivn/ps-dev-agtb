<?php 
 //WARNING: The contents of this file are auto-generated


// created: 2010-07-27 14:43:43
$dictionary["Orders"]["fields"]["orders_activities_emails"] = array (
  'name' => 'orders_activities_emails',
  'type' => 'link',
  'relationship' => 'orders_activities_emails',
  'source' => 'non-db',
);


// created: 2010-07-27 14:43:42
$dictionary["Orders"]["fields"]["orders_activities_meetings"] = array (
  'name' => 'orders_activities_meetings',
  'type' => 'link',
  'relationship' => 'orders_activities_meetings',
  'source' => 'non-db',
);


// created: 2010-07-26 08:58:38
$dictionary["Orders"]["fields"]["orders_contracts"] = array (
  'name' => 'orders_contracts',
  'type' => 'link',
  'relationship' => 'orders_contracts',
  'source' => 'non-db',
  'side' => 'right',
  'vname' => 'LBL_ORDERS_CONTRACTS_FROM_CONTRACTS_TITLE',
);


 // created: 2010-07-21 06:23:52
$dictionary['Orders']['fields']['notes']['duplicate_merge']='0';
$dictionary['Orders']['fields']['notes']['rows']='6';
$dictionary['Orders']['fields']['notes']['cols']='80';

 

// created: 2010-07-27 14:42:52
$dictionary["Orders"]["fields"]["orders_documents"] = array (
  'name' => 'orders_documents',
  'type' => 'link',
  'relationship' => 'orders_documents',
  'source' => 'non-db',
  'side' => 'right',
  'vname' => 'LBL_ORDERS_DOCUMENTS_FROM_DOCUMENTS_TITLE',
);


 // created: 2010-10-19 07:34:28
$dictionary['Orders']['fields']['in_netsuite_c']['enforced']='false';

 

// created: 2010-07-27 14:39:12
$dictionary["Orders"]["fields"]["orders_products"] = array (
  'name' => 'orders_products',
  'type' => 'link',
  'relationship' => 'orders_products',
  'source' => 'non-db',
  'side' => 'right',
  'vname' => 'LBL_ORDERS_PRODUCTS_FROM_PRODUCTS_TITLE',
);


 // created: 2010-07-21 06:23:52
$dictionary['Orders']['fields']['assigned_user_name']['required']='1';




// created: 2010-07-27 14:42:23
$dictionary["Orders"]["fields"]["discountcodes_orders"] = array (
  'name' => 'discountcodes_orders',
  'type' => 'link',
  'relationship' => 'discountcodes_orders',
  'source' => 'non-db',
  'vname' => 'LBL_DISCOUNTCODES_ORDERS_FROM_DISCOUNTCODES_TITLE',
);
$dictionary["Orders"]["fields"]["discountcodes_orders_name"] = array (
  'name' => 'discountcodes_orders_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_DISCOUNTCODES_ORDERS_FROM_DISCOUNTCODES_TITLE',
  'save' => true,
  'id_name' => 'discountco8a18ntcodes_ida',
  'link' => 'discountcodes_orders',
  'table' => 'discountcodes',
  'module' => 'DiscountCodes',
  'rname' => 'discount_code',
);
$dictionary["Orders"]["fields"]["discountco8a18ntcodes_ida"] = array (
  'name' => 'discountco8a18ntcodes_ida',
  'type' => 'link',
  'relationship' => 'discountcodes_orders',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'right',
  'vname' => 'LBL_DISCOUNTCODES_ORDERS_FROM_ORDERS_TITLE',
);


// created: 2010-08-23 08:04:50
$dictionary["Orders"]["fields"]["orders_subscriptions"] = array (
  'name' => 'orders_subscriptions',
  'type' => 'link',
  'relationship' => 'orders_subscriptions',
  'source' => 'non-db',
  'vname' => 'LBL_ORDERS_SUBSCRIPTIONS_FROM_SUBSCRIPTIONS_TITLE',
);
$dictionary["Orders"]["fields"]["orders_subscriptions_name"] = array (
  'name' => 'orders_subscriptions_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_ORDERS_SUBSCRIPTIONS_FROM_SUBSCRIPTIONS_TITLE',
  'save' => true,
  'id_name' => 'orders_subb9eaiptions_idb',
  'link' => 'orders_subscriptions',
  'table' => 'subscriptions',
  'module' => 'Subscriptions',
  'rname' => 'subscription_id',
);
$dictionary["Orders"]["fields"]["orders_subb9eaiptions_idb"] = array (
  'name' => 'orders_subb9eaiptions_idb',
  'type' => 'link',
  'relationship' => 'orders_subscriptions',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'left',
  'vname' => 'LBL_ORDERS_SUBSCRIPTIONS_FROM_SUBSCRIPTIONS_TITLE',
);


// created: 2010-07-27 14:43:43
$dictionary["Orders"]["fields"]["orders_activities_tasks"] = array (
  'name' => 'orders_activities_tasks',
  'type' => 'link',
  'relationship' => 'orders_activities_tasks',
  'source' => 'non-db',
);


// created: 2010-07-27 14:43:43
$dictionary["Orders"]["fields"]["orders_activities_notes"] = array (
  'name' => 'orders_activities_notes',
  'type' => 'link',
  'relationship' => 'orders_activities_notes',
  'source' => 'non-db',
);


// created: 2010-07-27 14:41:48
$dictionary["Orders"]["fields"]["contacts_orders"] = array (
  'name' => 'contacts_orders',
  'type' => 'link',
  'relationship' => 'contacts_orders',
  'source' => 'non-db',
  'vname' => 'LBL_CONTACTS_ORDERS_FROM_CONTACTS_TITLE',
);
$dictionary["Orders"]["fields"]["contacts_orders_name"] = array (
  'name' => 'contacts_orders_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_CONTACTS_ORDERS_FROM_CONTACTS_TITLE',
  'save' => true,
  'id_name' => 'contacts_o7603ontacts_ida',
  'link' => 'contacts_orders',
  'table' => 'contacts',
  'module' => 'Contacts',
  'rname' => 'name',
  'db_concat_fields' => 
  array (
    0 => 'first_name',
    1 => 'last_name',
  ),
);
$dictionary["Orders"]["fields"]["contacts_o7603ontacts_ida"] = array (
  'name' => 'contacts_o7603ontacts_ida',
  'type' => 'link',
  'relationship' => 'contacts_orders',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'right',
  'vname' => 'LBL_CONTACTS_ORDERS_FROM_ORDERS_TITLE',
);


// created: 2010-07-27 14:40:36
$dictionary["Orders"]["fields"]["accounts_orders"] = array (
  'name' => 'accounts_orders',
  'type' => 'link',
  'relationship' => 'accounts_orders',
  'source' => 'non-db',
  'vname' => 'LBL_ACCOUNTS_ORDERS_FROM_ACCOUNTS_TITLE',
);
$dictionary["Orders"]["fields"]["accounts_orders_name"] = array (
  'name' => 'accounts_orders_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_ACCOUNTS_ORDERS_FROM_ACCOUNTS_TITLE',
  'save' => true,
  'id_name' => 'accounts_od749ccounts_ida',
  'link' => 'accounts_orders',
  'table' => 'accounts',
  'module' => 'Accounts',
  'rname' => 'name',
);
$dictionary["Orders"]["fields"]["accounts_od749ccounts_ida"] = array (
  'name' => 'accounts_od749ccounts_ida',
  'type' => 'link',
  'relationship' => 'accounts_orders',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'right',
  'vname' => 'LBL_ACCOUNTS_ORDERS_FROM_ORDERS_TITLE',
);


 // created: 2010-08-23 08:34:41
$dictionary['Orders']['fields']['shipping_address_country']['options']='countries_dom';
$dictionary['Orders']['fields']['shipping_address_country']['dependency']=false;

 

// created: 2010-07-27 14:43:42
$dictionary["Orders"]["fields"]["orders_activities_calls"] = array (
  'name' => 'orders_activities_calls',
  'type' => 'link',
  'relationship' => 'orders_activities_calls',
  'source' => 'non-db',
);


 // created: 2010-10-19 23:10:26
$dictionary['Orders']['fields']['partner_margin_c']['enforced']='false';

 

// created: 2010-07-27 14:39:47
$dictionary["Orders"]["fields"]["orders_opportunities"] = array (
  'name' => 'orders_opportunities',
  'type' => 'link',
  'relationship' => 'orders_opportunities',
  'source' => 'non-db',
  'vname' => 'LBL_ORDERS_OPPORTUNITIES_FROM_OPPORTUNITIES_TITLE',
);
$dictionary["Orders"]["fields"]["orders_opportunities_name"] = array (
  'name' => 'orders_opportunities_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_ORDERS_OPPORTUNITIES_FROM_OPPORTUNITIES_TITLE',
  'save' => true,
  'id_name' => 'orders_opp02e0unities_idb',
  'link' => 'orders_opportunities',
  'table' => 'opportunities',
  'module' => 'Opportunities',
  'rname' => 'name',
);
$dictionary["Orders"]["fields"]["orders_opp02e0unities_idb"] = array (
  'name' => 'orders_opp02e0unities_idb',
  'type' => 'link',
  'relationship' => 'orders_opportunities',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'left',
  'vname' => 'LBL_ORDERS_OPPORTUNITIES_FROM_OPPORTUNITIES_TITLE',
);


 // created: 2010-07-20 17:08:03
$dictionary['Orders']['fields']['payment_method']['default_value']='credit_card';
$dictionary['Orders']['fields']['payment_method']['default']='credit_card';
$dictionary['Orders']['fields']['payment_method']['duplicate_merge']='0';

 

$dictionary['Orders']['fields']['shipping_address_country']['type'] = 'enum';
$dictionary['Orders']['fields']['shipping_address_country']['options'] = 'countries_dom';
$dictionary['Orders']['fields']['shipping_country']['type'] = 'enum';
$dictionary['Orders']['fields']['shipping_country']['options'] = 'countries_dom';
$dictionary['Orders']['fields']['billing_address_country']['type'] = 'enum';
$dictionary['Orders']['fields']['billing_address_country']['options'] = 'countries_dom';
$dictionary['Orders']['fields']['billing_country']['type'] = 'enum';
$dictionary['Orders']['fields']['billing_country']['options'] = 'countries_dom';



 // created: 2010-10-15 13:07:40
$dictionary['Orders']['fields']['status']['default']='pending_salesops';

 

 // created: 2010-10-25 12:37:18
$dictionary['Orders']['fields']['ondemand_instance_created_c']['enforced']='false';

 
?>