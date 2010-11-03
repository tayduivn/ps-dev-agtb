<?php 
 //WARNING: The contents of this file are auto-generated


// created: 2010-07-21 13:42:19
$dictionary["CR_Customer_Reference"]["fields"]["cr_customer_reference_accounts"] = array (
  'name' => 'cr_customer_reference_accounts',
  'type' => 'link',
  'relationship' => 'cr_customer_reference_accounts',
  'source' => 'non-db',
  'vname' => 'LBL_CR_CUSTOMER_REFERENCE_ACCOUNTS_FROM_ACCOUNTS_TITLE',
  'required' => true,
);
$dictionary["CR_Customer_Reference"]["fields"]["cr_customer_reference_accounts_name"] = array (
  'name' => 'cr_customer_reference_accounts_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_CR_CUSTOMER_REFERENCE_ACCOUNTS_FROM_ACCOUNTS_TITLE',
  'save' => true,
  'id_name' => 'cr_custome6b53ccounts_idb',
  'link' => 'cr_customer_reference_accounts',
  'table' => 'accounts',
  'module' => 'Accounts',
  'rname' => 'name',
  'required' => true,
);
$dictionary["CR_Customer_Reference"]["fields"]["cr_custome6b53ccounts_idb"] = array (
  'name' => 'cr_custome6b53ccounts_idb',
  'type' => 'link',
  'relationship' => 'cr_customer_reference_accounts',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'left',
  'vname' => 'LBL_CR_CUSTOMER_REFERENCE_ACCOUNTS_FROM_ACCOUNTS_TITLE',
  'required' => true,
);


// created: 2010-07-21 13:42:19
$dictionary["CR_Customer_Reference"]["fields"]["cr_customer_reference_contacts"] = array (
  'name' => 'cr_customer_reference_contacts',
  'type' => 'link',
  'relationship' => 'cr_customer_reference_contacts',
  'source' => 'non-db',
  'vname' => 'LBL_CR_CUSTOMER_REFERENCE_CONTACTS_FROM_CONTACTS_TITLE',
);
$dictionary["CR_Customer_Reference"]["fields"]["cr_customer_reference_contacts_name"] = array (
  'name' => 'cr_customer_reference_contacts_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_CR_CUSTOMER_REFERENCE_CONTACTS_FROM_CONTACTS_TITLE',
  'save' => true,
  'id_name' => 'cr_customebbd8ontacts_idb',
  'link' => 'cr_customer_reference_contacts',
  'table' => 'contacts',
  'module' => 'Contacts',
  'rname' => 'name',
  'db_concat_fields' => 
  array (
    0 => 'first_name',
    1 => 'last_name',
  ),
);
$dictionary["CR_Customer_Reference"]["fields"]["cr_customebbd8ontacts_idb"] = array (
  'name' => 'cr_customebbd8ontacts_idb',
  'type' => 'link',
  'relationship' => 'cr_customer_reference_contacts',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'left',
  'vname' => 'LBL_CR_CUSTOMER_REFERENCE_CONTACTS_FROM_CONTACTS_TITLE',
);


 // created: 2010-10-27 10:15:58
$dictionary['CR_Customer_Reference']['fields']['reference_type']['calculated']=false;

 

 // created: 2010-08-16 16:52:12

 

 // created: 2010-10-27 10:17:25
$dictionary['CR_Customer_Reference']['fields']['reference_activity']['calculated']=false;

 

 // created: 2010-09-14 14:13:08
$dictionary['CR_Customer_Reference']['fields']['reference_deliverables']['default']='^^';

 

 // created: 2010-10-27 10:14:59

 
?>