<?php

echo "<h4>Workflow Cleanup</h4><BR>";

$update = 
"UPDATE workflow LEFT JOIN workflow_triggershells ON workflow_triggershells.parent_id = workflow.id ".
"SET workflow_triggershells.eval = concat(\" ( isset(\$focus->\", workflow_triggershells.field, \") && \", workflow_triggershells.eval, \" ) \" ) ".
"WHERE workflow_triggershells.type = \"filter_field\" AND ".
    "( workflow_triggershells.deleted=0 OR workflow.parent_id IS NOT NULL AND workflow.parent_id !='') AND ".
    "workflow_triggershells.eval not like \"%isset(%\"";

//echo $update."<BR><BR>";

$result = $GLOBALS['db']->query($update);
$affected_rows = 0;
if($sugar_config['dbconfig']['db_type'] == 'mysql'){
	$affected_rows = $GLOBALS['db']->getAffectedRowCount();
}
else{
	$affected_rows = $GLOBALS['db']->getAffectedRowCount($result);
}

$update =
'update workflow_triggershells '.
'set eval = concat( "(isset($focus->", field, ") && (", "$focus->fetched_row[\'", field, "\'] != $focus->", field, "))" ) '.
'where type = \'compare_change\' and deleted = 0 and eval not like "%isset(%"';

$result = $GLOBALS['db']->query($update);
$affected_rows_two = 0;
if($sugar_config['dbconfig']['db_type'] == 'mysql'){
        $affected_rows_two = $GLOBALS['db']->getAffectedRowCount();
}
else{
        $affected_rows_two = $GLOBALS['db']->getAffectedRowCount($result);
}

//echo $update."<BR><BR>";

$query = 
"select workflow_triggershells.* ".
"FROM workflow ".
"LEFT JOIN workflow_triggershells ON workflow_triggershells.parent_id = workflow.id ".
"WHERE workflow_triggershells.type = 'compare_specific' ".
  "AND ( workflow_triggershells.deleted=0 OR workflow.parent_id IS NOT NULL AND workflow.parent_id !='') ".
  "AND workflow_triggershells.eval not like \"%isset(%\"";

//echo $query."<BR><BR>";

$result = $GLOBALS['db']->query($query);

$count = 0;
while($row = $GLOBALS['db']->fetchByAssoc($result)){
	$field = $row['field'];
	$eval = html_entity_decode($row['eval'], ENT_QUOTES);
	$eval = str_replace("\n", "", $eval);
	$match = '/\(\s*\$focus->'.$field.'\s*==\s*(.+)\s*\)/U';
	$replace = '(isset($focus->'.$field.') && ($focus->'.$field.' == \1) )';
	$ct = 0;
	$neweval = preg_replace($match, $replace, $eval, -1, $ct);
	if($eval == $neweval){
		continue;
	}
	$neweval = str_replace("\"", "\\\"", $neweval);
	
	//echo "\nTotal number replaced: $ct<BR>\n";
	//echo "\nReplacing<BR>\n".$match."<BR>\nwith<BR>\n".$replace."<BR>\n";
	//echo "\n<BR>\n$eval\n<BR>\n<BR>\n$neweval<BR>\n<BR>\n<BR>\n";
	//continue;
	
	$id = $row['id'];

	$updatequery = "update workflow_triggershells set eval = \"$neweval\" where id = \"$id\"";

	$updateres = $GLOBALS['db']->query($updatequery);

	$count++;
}



$query = 
"select workflow_triggershells.* ".
"FROM workflow ".
"LEFT JOIN workflow_triggershells ON workflow_triggershells.parent_id = workflow.id ".
"WHERE workflow_triggershells.type = 'compare_any_time' ".
  "AND ( workflow_triggershells.deleted=0 OR workflow.parent_id IS NOT NULL AND workflow.parent_id !='') ".
  "AND workflow_triggershells.eval not like \"%isset(\$focus%\"";

//echo $query."<BR><BR>";

$result = $GLOBALS['db']->query($query);

$count_two = 0;
while($row = $GLOBALS['db']->fetchByAssoc($result)){
	
	$field = $row['field'];
	$eval = html_entity_decode($row['eval'], ENT_QUOTES);
	$match = "isset(\$_SESSION['workflow_parameters'])";
	$replace = "isset(\$focus->{$row['field']}) && isset(\$_SESSION['workflow_parameters'])";
	$neweval = str_replace($match, $replace, $eval);
	
	$match_two = "\$focus->fetched_row['{$row['field']}'] != \$focus->{$row['field']}";
	$replace_two = "isset(\$focus->{$row['field']}) && \$focus->fetched_row['{$row['field']}'] != \$focus->{$row['field']}";
	$neweval = str_replace($match_two, $replace_two, $neweval);
	
	$match_three = "\$focus->fetched_row['{$row['field']}'] == \$focus->{$row['field']}";
	$replace_three = "isset(\$focus->{$row['field']}) && \$focus->fetched_row['{$row['field']}'] == \$focus->{$row['field']}";
	$neweval = str_replace($match_three, $replace_three, $neweval);
	
	$match_four = "\$focus->{$row['field']} == null";
	$replace_four = "!isset(\$focus->{$row['field']})";
	$neweval = str_replace($match_four, $replace_four, $neweval);
	
	if($eval == $neweval){
		continue;
	}
	$neweval = str_replace("\"", "\\\"", $neweval);
	
	//echo "\nReplacing<BR>\n".$match."<BR>\nwith<BR>\n".$replace."<BR>\n";
	//echo "\n<BR>\n$eval\n<BR>\n<BR>\n$neweval<BR>\n<BR>\n<BR>\n";
	//continue;
	
	$id = $row['id'];

	$updatequery = "update workflow_triggershells set eval = \"$neweval\" where id = \"$id\"";

	$updateres = $GLOBALS['db']->query($updatequery);

	$count_two++;
}

echo "Phase 1 of the workflow cleanup updated $affected_rows rows<BR>";
echo "Phase 2 of the workflow cleanup updated $affected_rows_two rows<BR>";
echo "Phase 3 of the workflow cleanup updated $count rows<BR>";
echo "Phase 4 of the workflow cleanup updated $count_two rows<BR>";

echo "<BR>Clean up script complete.<BR><BR>";

echo "To Rebuild the workflow files, please <a href=\"index.php?module=Administration&action=RebuildWorkFlow\">click here</a>.";

?>
