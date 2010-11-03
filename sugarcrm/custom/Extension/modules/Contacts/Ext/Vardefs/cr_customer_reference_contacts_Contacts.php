<?php
// created: 2010-07-21 13:42:19
$dictionary["Contact"]["fields"]["cr_customer_reference_contacts"] = array (
  'name' => 'cr_customer_reference_contacts',
  'type' => 'link',
  'relationship' => 'cr_customer_reference_contacts',
  'source' => 'non-db',
  'vname' => 'LBL_CR_CUSTOMER_REFERENCE_CONTACTS_FROM_CR_CUSTOMER_REFERENCE_TITLE',
);
$dictionary["Contact"]["fields"]["cr_customer_reference_contacts_name"] = array (
  'name' => 'cr_customer_reference_contacts_name',
  'type' => 'relate',
  'source' => 'non-db',
  'vname' => 'LBL_CR_CUSTOMER_REFERENCE_CONTACTS_FROM_CR_CUSTOMER_REFERENCE_TITLE',
  'save' => true,
  'id_name' => 'cr_custome89f1ference_ida',
  'link' => 'cr_customer_reference_contacts',
  'table' => 'cr_customer_reference',
  'module' => 'CR_Customer_Reference',
  'rname' => 'name',
);
$dictionary["Contact"]["fields"]["cr_custome89f1ference_ida"] = array (
  'name' => 'cr_custome89f1ference_ida',
  'type' => 'link',
  'relationship' => 'cr_customer_reference_contacts',
  'source' => 'non-db',
  'reportable' => false,
  'side' => 'left',
  'vname' => 'LBL_CR_CUSTOMER_REFERENCE_CONTACTS_FROM_CR_CUSTOMER_REFERENCE_TITLE',
);
