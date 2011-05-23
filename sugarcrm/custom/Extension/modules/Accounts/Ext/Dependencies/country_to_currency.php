<?php

require('custom/modules/Accounts/metadata/country_currency_map.php');

// Get the currency IDs and country codes
$currency_ids = array();

$query = "SELECT id, iso4217 FROM currencies WHERE iso4217 IN ('".implode("','", array_values($country_currency_map))."') AND deleted = 0 ORDER BY iso4217 asc";
$res = $GLOBALS['db']->query($query);
while($row = $GLOBALS['db']->fetchByAssoc($res)){
	if(in_array($row['iso4217'], $country_currency_map)){
		$currency_ids[$row['iso4217']] = $row['id'];
	}
}

$final_array = array();
foreach($country_currency_map as $coun => $cur){
	if(isset($currency_ids[$cur])){
		$final_array[$coun] = $currency_ids[$cur];
	}
}

// Create sugar logic lists
$country_list = 'createList("'.implode('","', array_keys($final_array)).'")';
$currency_list = 'createList("'.implode('","', array_values($final_array)).'")';

//$dependencies['Accounts']['country_to_currency']['trigger'] = 'isInList($billing_address_country, '.$country_list.')';
$dependencies['Accounts']['country_to_currency']['triggerFields'] = array('billing_address_country');
$dependencies['Accounts']['country_to_currency']['hooks'] = array('edit');
$dependencies['Accounts']['country_to_currency']['onload'] = false;

$dependencies['Accounts']['country_to_currency']['actions'][] = array(
	'name' => 'SetValue',
	'params' => array(
		'target' => 'currency_id',
		'value' => 'ifElse(isInList($billing_address_country, '.$country_list.'), valueAt(indexOf($billing_address_country, '.$country_list.'), '.$currency_list.'), "-99")',
	),
);
