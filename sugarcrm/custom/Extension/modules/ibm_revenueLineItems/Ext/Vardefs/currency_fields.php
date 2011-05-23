<?php

$dictionary['ibm_revenueLineItems']['fields']['ra_currency_id'] = array (
    'name' => 'ra_currency_id',
    'type' => 'id',
    'group'=>'currency_id',
    'vname' => 'LBL_RA_CURRENCY',
    'function'=>array('name'=>'getCurrencyDropDown', 'returns'=>'html'),
    'studio'=> 'visible',
    'reportable'=>false,
);
$dictionary['ibm_revenueLineItems']['fields']['ra_currency_name'] = array (
    'name'=>'ra_currency_name',
    'rname'=>'name',
    'id_name'=>'ra_currency_id',
    'vname'=>'LBL_RA_CURRENCY_NAME',
    'type'=>'relate',
    'isnull'=>'true',
    'table' => 'currencies',
    'module'=>'Currencies',
    'source' => 'non-db',
    'function'=>array('name'=>'getCurrencyNameDropDown', 'returns'=>'html'),
    'studio' => 'false',
    'duplicate_merge' => 'disabled',
);
$dictionary['ibm_revenueLineItems']['fields']['ra_currency_symbol'] = array (
    'name'=>'ra_currency_symbol',
    'rname'=>'symbol',
    'id_name'=>'ra_currency_id',
    'vname'=>'LBL_RA_CURRENCY_SYMBOL',
    'type'=>'relate',
    'isnull'=>'true',
    'table' => 'currencies',
    'module'=>'Currencies',
    'source' => 'non-db',
    'function'=>array('name'=>'getCurrencySymbolDropDown', 'returns'=>'html'),
    'studio' => 'false',
    'duplicate_merge' => 'disabled',
);



$dictionary['ibm_revenueLineItems']['fields']['fra_currency_id'] = array (
    'name' => 'fra_currency_id',
    'type' => 'id',
    'group'=>'currency_id',
    'vname' => 'LBL_FRA_CURRENCY',
    'function'=>array('name'=>'getCurrencyDropDown', 'returns'=>'html'),
    'studio'=> 'visible',
    'reportable'=>false,
);
$dictionary['ibm_revenueLineItems']['fields']['fra_currency_name'] = array (
    'name'=>'fra_currency_name',
    'rname'=>'name',
    'id_name'=>'fra_currency_id',
    'vname'=>'LBL_FRA_CURRENCY_NAME',
    'type'=>'relate',
    'isnull'=>'true',
    'table' => 'currencies',
    'module'=>'Currencies',
    'source' => 'non-db',
    'function'=>array('name'=>'getCurrencyNameDropDown', 'returns'=>'html'),
    'studio' => 'false',
    'duplicate_merge' => 'disabled',
);
$dictionary['ibm_revenueLineItems']['fields']['fra_currency_symbol'] = array (
    'name'=>'fra_currency_symbol',
    'rname'=>'symbol',
    'id_name'=>'fra_currency_id',
    'vname'=>'LBL_FRA_CURRENCY_SYMBOL',
    'type'=>'relate',
    'isnull'=>'true',
    'table' => 'currencies',
    'module'=>'Currencies',
    'source' => 'non-db',
    'function'=>array('name'=>'getCurrencySymbolDropDown', 'returns'=>'html'),
    'studio' => 'false',
    'duplicate_merge' => 'disabled',
);
