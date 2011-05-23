<?php

if(!defined('sugarEntry')){
	define('sugarEntry', true);
}
require_once('include/entryPoint.php');

$currency_meta = array(
	'ARS' => '0.000001',
	'AUD' => '1.6129032',
	'EUR' => '1.0000000',
	'EUR' => '1.0000000',
	'BOB' => '0.000001',
	'BWP' => '5.5000000',
	'BRL' => '2.3000000',
	'BGN' => '0.000001',
	'CAD' => '1.5000000',
	'CLP' => '0.000001',
	'CNY' => '8.4000000',
	'COP' => '0.000001',
	'HRK' => '0.000001',
	'EUR' => '1.0000000',
	'CZK' => '20.0000000',
	'DKK' => '7.6000000',
	'AED' => '3.6724000',
	'ECS' => '0.000001',
	'EGP' => '0.000001',
	'EUR' => '1.0000000',
	'EUR' => '1.0000000',
	'EUR' => '1.0000000',
	'EUR' => '1.0000000',
	'EUR' => '1.0000000',
	'EUR' => '1.0000000',
	'HKD' => '7.8000000',
	'HUF' => '0.000001',
	'ISK' => '87.5000000',
	'INR' => '45.0000000',
	'IDR' => '0.000001',
	'EUR' => '1.0000000',
	'ILS' => '4.1000000',
	'EUR' => '1.0000000',
	'JPY' => '105.0000000',
	'LVL' => '0.5850000',
	'LTL' => '4.0000000',
	'EUR' => '1.0000000',
	'MYR' => '3.5000000',
	'MXN' => '0.000001',
	'MAD' => '0.000001',
	'EUR' => '1.0000000',
	'NZD' => '2.0000000',
	'NOK' => '8.3000000',
	'PKR' => '0.000001',
	'PYG' => '0.000001',
	'PEN' => '0.000001',
	'PHP' => '0.000001',
	'PLN' => '3.0000000',
	'EUR' => '1.0000000',
	'RON' => '0.000001',
	'RUB' => '0.000001',
	'SGD' => '0.000001',
	'EUR' => '1.0000000',
	'EUR' => '1.0000000',
	'EUR' => '1.0000000',
	'LKR' => '0.000001',
	'SEK' => '8.6000000',
	'CHF' => '1.6300000',
	'ZAR' => '7.0000000',
	'KRW' => '1000.0000000',
	'TWD' => '30.8000000',
	'THB' => '34.0000000',
	'TRY' => '0.000001',
	'TND' => '0.000001',
	'UYU' => '0.000001',
	'GBP' => '0.6451613',
	'VEF' => '0.000001',
	'VND' => '0.000001',
	'ZWD' => '0.000001',
);

$name_meta = array(
	'ARS' => 'Peso',
	'AUD' => 'Dollar',
	'EUR' => 'EURO',
	'EUR' => 'EURO',
	'BOB' => 'Bolivianos',
	'BWP' => 'Pula',
	'BRL' => 'Real',
	'BGN' => 'Lev',
	'CAD' => 'Dollar',
	'CLP' => 'Peso',
	'CNY' => 'Renminbi',
	'COP' => 'Peso',
	'HRK' => 'Kuna',
	'EUR' => 'EURO',
	'CZK' => 'Koruna',
	'DKK' => 'Krone',
	'AED' => 'Dirham',
	'ECS' => 'Sucre',
	'EGP' => 'Pound',
	'EUR' => 'EURO',
	'EUR' => 'EURO',
	'EUR' => 'EURO',
	'EUR' => 'EURO',
	'EUR' => 'EURO',
	'EUR' => 'EURO',
	'HKD' => 'Dollar',
	'HUF' => 'Forint',
	'ISK' => 'Krona',
	'INR' => 'Rupee',
	'IDR' => 'Rupiah',
	'EUR' => 'EURO',
	'ILS' => 'Shekel',
	'EUR' => 'EURO',
	'JPY' => 'Yen',
	'LVL' => 'Lat',
	'LTL' => 'Lit',
	'EUR' => 'EURO',
	'MYR' => 'Ringgit',
	'MXN' => 'Peso',
	'MAD' => 'Dirham',
	'EUR' => 'EURO',
	'NZD' => 'Dollar',
	'NOK' => 'Krone',
	'PKR' => 'Rupee',
	'PYG' => 'Guarai',
	'PEN' => 'New Sol',
	'PHP' => 'Peso',
	'PLN' => 'Zloty',
	'EUR' => 'EURO',
	'RON' => 'Lei',
	'RUB' => 'Ruble',
	'SGD' => 'Dollar',
	'EUR' => 'EURO',
	'EUR' => 'EURO',
	'EUR' => 'EURO',
	'LKR' => 'Rupee',
	'SEK' => 'Krona',
	'CHF' => 'Franc',
	'ZAR' => 'Rand',
	'KRW' => 'Won',
	'TWD' => 'Dollar',
	'THB' => 'Baht',
	'TRY' => 'Lira',
	'TND' => 'Dinar',
	'UYU' => 'New Peso',
	'GBP' => 'Pound',
	'VEF' => 'Bolivar',
	'VND' => 'Dong',
	'ZWD' => 'Dollar',
);


// Move this script to the top level and execute once. This will create all the currency objects

require_once('modules/Currencies/Currency.php');

$already_saved = array();
$res = $GLOBALS['db']->query("select id from currencies where iso4217='EUR' and deleted = 0");
if($res){
	$row = $GLOBALS['db']->fetchByAssoc($res);
	if($row){
		$already_saved['EUR'] = 'EUR';
	}
}
foreach($currency_meta as $currency_code => $rate){
	if(in_array($currency_code, $already_saved)){
		continue;
	}
	
	// flag it so we don't re insert it
	$already_saved[$currency_code] = $currency_code;
	
	$currency = new Currency();
	if(!empty($name_meta[$currency_code])){
		$currency->name = $name_meta[$currency_code];
	}
	else{
		$currency->name = $currency_code;
	}
	$currency->status = 'Active';
	$currency->conversion_rate = $rate;
	$currency->symbol = $currency_code;
	$currency->iso4217 = $currency_code;
	$currency->save();
}
