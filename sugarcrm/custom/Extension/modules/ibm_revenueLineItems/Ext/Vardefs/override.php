<?php

$dictionary["ibm_revenueLineItems"]["fields"]["name"]["required"] = false;

// revenue items search - jvink
$dictionary["ibm_revenueLineItems"]["fields"]["name"]["revitems_level"]="0";
$dictionary["ibm_revenueLineItems"]["fields"]["name"]["revitems_level_ibm"]="0";
// define which field to setup for our search type
$dictionary["ibm_revenueLineItems"]["fields"]["name"]["revitems_search_type_field"]="search_type";
$dictionary["ibm_revenueLineItems"]["fields"]["offering_type"]["revitems_level"]="1";
$dictionary["ibm_revenueLineItems"]["fields"]["offering_type"]["revitems_level_ibm"]="10";
$dictionary["ibm_revenueLineItems"]["fields"]["sub_brand_c"]["revitems_level"]="2";
$dictionary["ibm_revenueLineItems"]["fields"]["sub_brand_c"]["revitems_level_ibm"]="15";
$dictionary["ibm_revenueLineItems"]["fields"]["brand_code"]["revitems_level"]="3";
$dictionary["ibm_revenueLineItems"]["fields"]["brand_code"]["revitems_level_ibm"]="20";
$dictionary["ibm_revenueLineItems"]["fields"]["product_information"]["revitems_level"]="4";
$dictionary["ibm_revenueLineItems"]["fields"]["product_information"]["revitems_level_ibm"]="30";
$dictionary["ibm_revenueLineItems"]["fields"]["machine_type"]["revitems_level"]="5";
$dictionary["ibm_revenueLineItems"]["fields"]["machine_type"]["revitems_level_ibm"]="40";

// what are you looking for field - jvink
$dictionary["ibm_revenueLineItems"]["fields"]["search_type"] = array (
	'name' => 'search_type',
	'type' => 'varchar',
	'len' => '100',
);

// refurb unchecked by default
$dictionary["ibm_revenueLineItems"]["fields"]["refurb"]["default"] = 0;

// START jvink - enable favorites for forecasting
$dictionary["ibm_revenueLineItems"]["favorites"] = true;

