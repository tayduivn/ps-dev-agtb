<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

if(!file_exists("custom/modules/Administration/CountryAbbreviationMap.php")){
	sugar_die("custom/modules/Administration/CountryAbbreviationMap.php does not exist. Please ensure it is there before proceeding.");
}
require_once("custom/modules/Administration/CountryAbbreviationMap.php");

$countryFields = array(
	'Accounts' => array(
		'billing_address_country',
		'shipping_address_country',
	),
	'Contacts' => array(
		'primary_address_country',
		'alt_address_country',
	),
	'Users' => array(
		'address_country',
	),
	'Leads' => array(
		'primary_address_country',
		'alt_address_country',
	),
	'Prospects' => array(
		'primary_address_country',
		'alt_address_country',
	),
	'Quotes' => array(
		'billing_address_country',
		'shipping_address_country',
	),
);

echo "<form method=post action=index.php?module=Administration&action=CountryConvertToDropdown>\n";
echo "<h3>Convert Country fields to Dropdown values</h3>";
if(!isset($_POST['field'])){
	echo "<h4>Please select a field from a module</h4>\n";
	foreach($countryFields as $module => $arr){
		echo "$module<BR>\n";
		foreach($arr as $field){
			echo "<input type=radio name=\"field\" value=\"$module---$field\">$field<BR>\n";
		}
	}
	echo "<BR><BR><input type=submit value=submit>\n";
}
else if(!isset($_POST['000RunIt000'])){
	list($_POST['moduleWithDropdown'], $_POST['field']) = explode("---", $_POST['field']);
	$query = "select {$_POST['field']} from ".strtolower($_POST['moduleWithDropdown']);

	$res = $GLOBALS['db']->query($query);
	
	echo "<h4>{$_POST['moduleWithDropdown']} - {$_POST['field']}</h4>";
	echo "<BR>The following values don't match any of the country dropdown values. Please map them to the correct value and submit to update them.<BR>\n";
	echo "<input type=hidden name=moduleWithDropdown value=\"{$_POST['moduleWithDropdown']}\">\n";
	echo "<input type=hidden name=field value=\"{$_POST['field']}\">\n";
	echo "<input type=hidden name=000RunIt000 value=\"{$_POST['field']}\">\n";
	
	echo "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" class=\"tabDetailView\">\n";
	echo "\t<tr>\n\t\t<th>Current Value</th><th>New Value</td><th>Update Value</td>\n\t</tr>\n";
	$i = 0;
	$arrayOfVals = array();
	while($row = $GLOBALS['db']->fetchByAssoc($res)){
		if(!empty($row[$_POST['field']]) && in_array($row[$_POST['field']], $app_list_strings['countries_dom'])){
			continue;
		}
		if(in_array($row[$_POST['field']], $arrayOfVals)){
			continue;
		}
		else{
			$arrayOfVals[$row[$_POST['field']]] = $row[$_POST['field']];
		}
		
		if(empty($row[$_POST['field']])){
			echo "\t\t<td><i>Empty String</i></td>\n";
			$row[$_POST['field']] = "ANEMPTYSTRING";
		}
		else{
			echo "\t\t<td>{$row[$_POST['field']]}</td>\n";
		}
		
		$selected = strtoupper($row[$_POST['field']]);
		if(array_key_exists($selected, $abbreviation_map)){
			$selected = strtoupper($abbreviation_map[$selected]);
		}
		$selected = str_replace("_", " ", $selected);
		$selected = str_replace("-", " ", $selected);
		$selected = trim($selected);
		
		$row[$_POST['field']] = str_replace(":", "___", $row[$_POST['field']]);
		$row[$_POST['field']] = str_replace(".", ":::::::", $row[$_POST['field']]);
		$row[$_POST['field']] = str_replace(" ", "::::::", $row[$_POST['field']]);
		echo "\t\t<td>\n";
		echo "\t\t\t<select name=\"".$row[$_POST['field']]."\">";
		echo get_select_options_with_id ($app_list_strings['countries_dom'], $selected);
		echo "\t\t</td>\n";
		echo "\t\t<td>\n";
		echo "\t\t\t<input type=checkbox name=\"checkboxcheckbox-".$row[$_POST['field']]."\" checked>";
		echo "\t\t</td>\n";
		echo "\t<tr>\n";
		$i++;
	}
	echo "</table>\n";
	echo "<BR><BR><input type=submit value=\"Submit Changes\">\n";
}
else{
	//echo "<PRE>";
	//print_r($_POST);
	//die();
	$moduleToUpdate = strtolower($_POST['moduleWithDropdown']);
	unset($_POST['moduleWithDropdown']);
	$fieldToUpdate = $_POST['field'];
	unset($_POST['field']);
	unset($_POST['000RunIt000']);

	foreach($_POST as $oldvalue => $newvalue){
		if(substr($oldvalue, 0, 17) == "checkboxcheckbox-"){
			continue;
		}
		if(isset($_POST["checkboxcheckbox-$oldvalue"]) && $_POST["checkboxcheckbox-$oldvalue"] == "on"){
			$oldvalue = str_replace(":::::::", ".", $oldvalue);
			$newvalue = str_replace(":::::::", ".", $newvalue);
			$oldvalue = str_replace("::::::", " ", $oldvalue);
			$newvalue = str_replace("::::::", " ", $newvalue);
			$oldvalue = str_replace("___", ":", $oldvalue);
			$newvalue = str_replace("___", ":", $newvalue);
			$oldvalue = str_replace("ANEMPTYSTRING", "", $oldvalue);
			$newvalue = str_replace("ANEMPTYSTRING", "", $newvalue);
			
			$query = "update $moduleToUpdate set $fieldToUpdate='".$GLOBALS['db']->quote($newvalue)."' where $fieldToUpdate='".$GLOBALS['db']->quote($oldvalue)."'";
			//Uncomment the line below to execute the queries
			$GLOBALS['db']->query($query);
			echo "EXECUTED :: $query<BR>";
		}
		else{
			$oldvalue = str_replace("::::::", " ", $oldvalue);
			echo "Skipped $oldvalue<BR>";
		}
	}
	
	echo "<BR><i>All updates complete!</i><BR>";
}
echo "</form>\n";


