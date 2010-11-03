<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

if (!is_admin($current_user)) sugar_die("Unauthorized access to administration.");

$badtables = getTableNullColumn('assigned_user_id');

echo "<h3>The following tables have null assigned_user_id values</h3>\n";

if(empty($badtables)){
	echo "No tables found with null assigned_user_id values<BR><BR>\n";
}
else{
	echoReassignForm($badtables);
}

function getTableNullColumn($column){
	$result = mysql_query("show tables");
	$tablearr = array();
	while($row = mysql_fetch_row($result))
	{
		$foundField = false;
		$inres = mysql_query("desc " . $row[0]);
		while($inrow = mysql_fetch_row($inres)){
			if($inrow[0] == $column){
				$foundField = true;
				break;
			}
		}
		if($foundField)
			$tablearr[] = $row[0];
	}

	$badtables = array();
	foreach($tablearr as $table){
		$count = mysql_fetch_row(mysql_query("select count(*) from $table where $column is null"));
		if($count[0] > 0)
			$badtables[] = $table;
	}

	return $badtables;
}

function echoReassignForm($badtables){
	$json = getJSONobj();
	require_once('include/QuickSearchDefaults.php');
	$qsd = new QuickSearchDefaults();

	$sqs_objects = array();
	foreach($badtables as $table){
		$sqs_objects[$table.'::assigned_user_name'] = $qsd->getQSUser();
		foreach($sqs_objects[$table.'::assigned_user_name']['populate_list'] as $ndx => $val){
			$sqs_objects[$table.'::assigned_user_name']['populate_list'][$ndx] = $table."::".$val;
		}
	}
	
	echo "<script type=\"text/javascript\" language=\"javascript\" src=\"include/javascript/popup_parent_helper.js\"></script>\n";
	$quicksearch_js = $qsd->getQSScripts();
	$quicksearch_js .= '<script type="text/javascript" language="javascript">sqs_objects = ' . $json->encode($sqs_objects) . '</script>' . "\n";
	echo $quicksearch_js;
	echo get_set_focus_js();
	require_once('include/javascript/javascript.php');
	$javascript = new javascript();
	echo $javascript->getScript();
	
	echo 
	'
<form method=post action="index.php?module=Administration&action=reassignNullRecords">
<table width="60%" border="0" cellspacing="0" cellpadding="0"  class="tabDetailView">
<tr>
<th class="tabDetailViewDL"><center>Table</center></th>
<th class="tabDetailViewDL"><center>Assign To</center></th>
<th class="tabDetailViewDL"><center>&nbsp;</center></th>
<tr><td>&nbsp;</td><td>&nbsp;</td></tr>
	';

	$slotnum = 0;
	foreach($badtables as $table)
	{
		$popup_request_data = array(
			'call_back_function' => 'set_return',
			'form_name' => 'EditView',
				'field_to_name_array' => array(
					'id' => $table.'::assigned_user_id',
					'user_name' => $table.'::assigned_user_name',
				),
			);
	
		echo "<tr><td width=\"35%\" class=\"dataLabel\">$table</td>\n";
		echo '
        <td width=\"65%\" class="dataField"><span sugar=\'slot'.$slotnum.'b\'>
		<input class="sqsEnabled" tabindex="'.($slotnum + 1).'" autocomplete="off" id="'.$table.'::assigned_user_name" name=\''.$table.'::assigned_user_name\' type="text" >
		<input id=\''.$table.'::assigned_user_id\' name=\''.$table.'::assigned_user_id\' type="hidden" />&nbsp;
		<input title="Select" type="button" tabindex=\'2\' class="button" value=\'Select\' name=btn'.$table.'
                        onclick=\'open_popup("Users", 600, 400, "", true, false, '.$json->encode($popup_request_data).');\' /></span sugar=\'slot\'>
        </td>
		';

		echo "</tr>\n";
		$slotnum++;
	}
	echo "<tr><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
	echo "<tr><td><input type=submit name=reassignbtn value=Reassign></td><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
	echo "<tr><td>&nbsp;</td><td>&nbsp;</td></tr>\n";
	echo "</table>\n";
	echo "</form>\n";

}

?>

