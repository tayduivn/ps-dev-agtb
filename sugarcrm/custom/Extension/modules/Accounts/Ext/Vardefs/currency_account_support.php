<?php

$dictionary['Account']['fields']['currency_id'] = array (
	'name' => 'currency_id',
	'type' => 'id',
	'group'=>'currency_id',
	'vname' => 'LBL_CURRENCY',
	'function'=>array('name'=>'getCurrencyDropDown', 'returns'=>'html'),
	'studio'=> 'visible',
	'reportable'=>false,
	'comment' => 'Currency used for display purposes'
);
$dictionary['Account']['fields']['currency_name'] = array (
	'name'=>'currency_name',
	'rname'=>'name',
	'id_name'=>'currency_id',
	'vname'=>'LBL_CURRENCY_NAME',
	'type'=>'relate',
	'isnull'=>'true',
	'table' => 'currencies',
	'module'=>'Currencies',
	'source' => 'non-db',
	'function'=>array('name'=>'getCurrencyNameDropDown', 'returns'=>'html'),
	'studio' => 'false',
	'duplicate_merge' => 'disabled',
);
$dictionary['Account']['fields']['currency_symbol'] = array (
	'name'=>'currency_symbol',
	'rname'=>'symbol',
	'id_name'=>'currency_id',
	'vname'=>'LBL_CURRENCY_SYMBOL',
	'type'=>'relate',
	'isnull'=>'true',
	'table' => 'currencies',
	'module'=>'Currencies',
	'source' => 'non-db',
	'function'=>array('name'=>'getCurrencySymbolDropDown', 'returns'=>'html'),
	'studio' => 'false',
	'duplicate_merge' => 'disabled',
);
$dictionary['Account']['fields']['currencies'] = array (
	'name' => 'currencies',
	'type' => 'link',
	'relationship' => 'account_currencies',
	'source'=>'non-db',
	'vname'=>'LBL_CURRENCIES',
);
$dictionary['Account']['relationships']['account_currencies'] = array(
	'lhs_module'=> 'Accounts',
	'lhs_table'=> 'accounts',
	'lhs_key' => 'currency_id',
	'rhs_module'=> 'Currencies',
	'rhs_table'=> 'currencies',
	'rhs_key' => 'id',
	'relationship_type'=>'one-to-many',
);
