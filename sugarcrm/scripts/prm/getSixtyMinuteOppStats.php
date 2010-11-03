<?php
$db = mysql_connect('si-db2', 'slave_select', 'iv1aikewi(') or die(mysql_error());
mysql_select_db('sugarinternal');

$i = 0;
$res = mysql_query("SELECT DISTINCT parent_id AS opp_id FROM opportunities_audit WHERE field_name = 'sixtymin_opp_c' ORDER BY RAND()") or die(mysql_error());
while ($row = mysql_fetch_assoc($res)) {
	$res2 = mysql_query("SELECT * FROM opportunities_audit WHERE parent_id = '{$row['opp_id']}' ORDER BY date_created ASC");
	echo $row['opp_id'] . " ..... \n";
	while ($row2 = mysql_fetch_assoc($res2)) {
		echo "{$row2['date_created']}: {$row2['field_name']} ... {$row2['before_value_string']} -> {$row2['after_value_string']}\n";
	}

	echo "\n\n";

	$i++;
	if ($i > 5) die();
}
