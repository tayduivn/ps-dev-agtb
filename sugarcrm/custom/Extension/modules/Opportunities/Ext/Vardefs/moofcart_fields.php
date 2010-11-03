<?php
// BEGIN jostrow MoofCart customization
// See ITRequest #9622

$dictionary['Opportunity']['fields']['discount_code_c'] = array(
	'name' => 'discount_code_c',
	'vname' => 'LBL_DISCOUNT_CODE',
	'type' => 'varchar',
	'dbType' => 'varchar',
	'len' => 100,
	'unified_search' => FALSE,
	'help' => 'This discount code will automatically be applied to a customer\'s shopping cart when they click a link from a Renewal Notice e-mail',
	'audited' => TRUE,
);

// END jostrow MoofCart customization
