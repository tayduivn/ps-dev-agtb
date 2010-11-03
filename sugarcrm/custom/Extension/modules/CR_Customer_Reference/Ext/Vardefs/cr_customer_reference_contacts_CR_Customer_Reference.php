<?php
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
