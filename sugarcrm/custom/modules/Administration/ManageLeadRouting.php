<?php

if(!defined('sugarEntry') || !sugarEntry) die('Not a Valid Entry Point');

$header_display = 'Manage Lead Routing';
echo "<h2>$header_display</h2><BR>";

$routingMetaFile = 'custom/si_custom_files/meta/leadRoutingMeta.php';

if(!isset($_POST['setnewrules'])){
	echo "Please wait for about 10 seconds after the page loads completely before attempting to enter values.<BR><BR>";
	$user_array = get_user_array(false);
	require($routingMetaFile);
	echo "
<form method=post action='{$_SERVER['REQUEST_URI']}' name=newrules>
<input type=submit name=setnewrules value=Submit>
<table border='0' cellpadding='0' cellspacing='0'>
";
	$json = getJSONobj();
	require_once('include/QuickSearchDefaults.php');
	$qsd = new QuickSearchDefaults();
	$sqs_objects = array();
	if(!isset($leadBreakdownMap['Inside'])) $leadBreakdownMap['Inside'] = array();
	if(!isset($leadBreakdownMap['Corporate'])) $leadBreakdownMap['Corporate'] = array();
	if(!isset($leadBreakdownMap['Enterprise'])) $leadBreakdownMap['Enterprise'] = array();
	foreach($leadBreakdownMap as $group => $groupArr){
		$groupArr = array_merge($app_list_strings['countries_dom'], $groupArr);
		foreach($groupArr as $country => $countryValOrArr){
			if(empty($country)){
				continue;
			}
			if(!is_array($countryValOrArr)){
				$id = 'country+++'.$group.'+++assigned_user_id+++'.$country;
				$user_name = 'country+++'.$group.'+++assigned_user_name+++'.$country;
				$sqs_objects[$user_name] = $qsd->getQSUser();
				foreach($sqs_objects[$user_name]['populate_list'] as $ndx => $val){
					$sqs_objects[$user_name]['populate_list'][$ndx] = "country+++$group+++$val+++$country";
				}
			}
			else{
				foreach($countryValOrArr as $state => $routedTo){
					$id = 'countrystate+++'.$group.'+++assigned_user_id+++'.$country.'+++'.$state;
					$user_name = 'countrystate+++'.$group.'+++assigned_user_name+++'.$country.'+++'.$state;
					$sqs_objects[$user_name] = $qsd->getQSUser();
					foreach($sqs_objects[$user_name]['populate_list'] as $ndx => $val){
						$sqs_objects[$user_name]['populate_list'][$ndx] = "countrystate+++$group+++$val+++$country+++$state";
					}
				}
			}
		}
	}
	$quicksearch_js = $qsd->getQSScripts();
    $quicksearch_js .= '<script type="text/javascript" language="javascript">sqs_objects = ' . $json->encode($sqs_objects) . '</script>' . "\n";
    echo $quicksearch_js;
    echo get_set_focus_js();

	foreach($leadBreakdownMap as $group => $groupArr){
		echo "<tr><td width='10%' class='tabDetailViewDL'><b><font size=3>$group</font></b></td></tr>";
		$groupArr = array_merge($app_list_strings['countries_dom'], $groupArr);
		foreach($groupArr as $country => $countryValOrArr){
			if(empty($country)){
				continue;
			}
			echo "<tr>";
			if(!is_array($countryValOrArr)){
				echo "<td width='10%' class='tabDetailViewDL'>$country</td>";
				$id = 'country+++'.$group.'+++assigned_user_id+++'.$country;
				$user_name = 'country+++'.$group.'+++assigned_user_name+++'.$country;
				$button = $country;
				$id_val = '';
				$name_val = '';
				if(!empty($user_array[$countryValOrArr])){
					$id_val = $countryValOrArr;
					$name_val = $user_array[$countryValOrArr];
				}
				echoQuickSearch($id, $user_name, $button, $json, $id_val, $name_val);
			}
			else{
				foreach($countryValOrArr as $state => $routedTo){
					echo "<td width='10%' class='tabDetailViewDL'>$country: $state</td>";
					$id = 'countrystate+++'.$group.'+++assigned_user_id+++'.$country.'+++'.$state;
					$user_name = 'countrystate+++'.$group.'+++assigned_user_name+++'.$country.'+++'.$state;
					$button = "{$country}_{$state}";
					$id_val = '';
					$name_val = '';
					if(!empty($user_array[$routedTo])){
						$id_val = $routedTo;
						$name_val = $user_array[$routedTo];
					}
					echoQuickSearch($id, $user_name, $button, $json, $id_val, $name_val);
					echo "</tr><tr>\n";
				}
			}
			echo "</tr>\n";
		}
	}
	echo "</table>\n";
	echo "<input type=submit name=setnewrules value=Submit>\n";
	echo "</form>\n";
}
else{
	require($routingMetaFile);
	foreach($_POST as $key => $assigned_user_id){
		if($key == 'setnewrules' || strpos($key, 'assigned_user_name') !== false)
			continue;
		
		$values = parseTheDataFromKey($key);
		if(strpos($values['country'], "ST_") === 0){
			$values['country'] = str_replace("ST_", "ST.", $values['country']);
		}
		$values['country'] = str_replace("_", " ", $values['country']);
		if(isset($values['state'])){
			$leadBreakdownMap[$values['group']][$values['country']][$values['state']] = $assigned_user_id;
		}
		else{
			$leadBreakdownMap[$values['group']][$values['country']] = $assigned_user_id;
		}
	}
	$counter = 0;
	while(file_exists($routingMetaFile.".bak.".$counter)){
		$counter++;
	}
	copy($routingMetaFile, $routingMetaFile.".bak.".$counter);
	$file_header = '<?php'."\n".'// Updated by '.$GLOBALS['current_user']->user_name.' at '.date('Y-m-d H:i:s')."\n";
	write_array_to_file( "leadBreakdownMap", $leadBreakdownMap, $routingMetaFile, 'w', $file_header );
	echo "Successfully saved mapping. Click <a href='{$_SERVER['REQUEST_URI']}'>here</a> to go back.";
}

function parseTheDataFromKey($key){
	if(strpos($key, "country") === false){
		return array();
	}
	
	$results = explode("+++", $key);
	$returnData['group'] = $results[1];
	$returnData['field'] = $results[2];
	$returnData['country'] = $results[3];
	if($results[0] == 'countrystate')
		$returnData['state'] = $results[4];
	
	return $returnData;
}

function echoQuickSearch($id, $user_name, $button, &$json, $id_val = '', $name_val = ''){
	$popup_request_data = array(
		'call_back_function' => 'set_return',
		'form_name' => 'EditView',
		'field_to_name_array' => array(
			'id' => $id,
			'user_name' => $user_name,
		),
	);
	echo '<td class="dataField">
<input class="sqsEnabled" autocomplete="off" id="'.$user_name.'" name=\''.$user_name.'\' type="text" '.(!empty($name_val) ? "value='$name_val'" : '').'>
<input id=\''.$id.'\' name=\''.$id.'\' type="hidden" '.(!empty($id_val) ? "value='$id_val'" : '').'/>&nbsp;
<input title="Select" type="button" class="button" value=\'Select\' name=btn'.$button.' onclick=\'open_popup("Users", 600, 400, "", true, false, '.$json->encode($popup_request_data).');\' />
</td>';
}
