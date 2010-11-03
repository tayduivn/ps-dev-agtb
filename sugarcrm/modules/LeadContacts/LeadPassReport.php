<?php

if(!defined('sugarEntry')) die("Not a valid entry point");

$page_output = '';
if(!isset($_REQUEST['do_export'])){
	$page_output = "<h2>Lead Pass Report</h2><BR>";
}

if(!isset($_POST['year'])){
$output =<<<EOQ
<form name=LeadPassForm method=post action="{$_SERVER['REQUEST_URI']}">
<table border="0" cellpadding="0" cellspacing="0" width="50%">
	<tr>
	<td width="90%" class="tabDetailViewDL">
	Please select the year you would like to filter on:
	</td>
	<td width="10%" class="tabDetailViewDF">
	<select name=year>
	<option value=2008>2008</option>
	<option value=2009 selected>2009</option>
	<option value=2010>2010</option>
	<option value=2011>2011</option>
	<option value=2012>2012</option>
	</select>
	</td>
	</tr>
	<tr>
	<td width="90%" class="tabDetailViewDL">
	Please select the time period you would like to filter on:
	</td>
	<td width="10%" class="tabDetailViewDF">
	<select name=month>
	<option value=Q1>Q1</option>
	<option value=Q2>Q2</option>
	<option value=Q3>Q3</option>
	<option value=Q4>Q4</option>
	<option value=01>Jan</option>
	<option value=02>Feb</option>
	<option value=03>Mar</option>
	<option value=04>Apr</option>
	<option value=05>May</option>
	<option value=06>Jun</option>
	<option value=07>Jul</option>
	<option value=08>Aug</option>
	<option value=09>Sep</option>
	<option value=10>Oct</option>
	<option value=11>Nov</option>
	<option value=12>Dec</option>
	</select>
	</td>
	</tr>
</table>
<BR>
<input type=hidden name=to_pdf value=0>
<input type=submit value="Run Report">&nbsp;<input type=submit name=do_export value="Export" onClick="document.LeadPassForm.to_pdf.value='1';">
</form>
EOQ;
	$page_output .= $output;
	echo $page_output;
	sugar_die('');
}

$export = false;
if(isset($_REQUEST['do_export'])){
	$export = true;
}

$date_clause = '';
$start_date = '';
$end_date = '';
switch($_REQUEST['month']){
    case 'Q1':
        $start_date = mktime(0,0,0,1,1,$_REQUEST['year']);
        $end_date = mktime(0,0,0,4,1,$_REQUEST['year']);
        break;
    case 'Q2':
        $start_date = mktime(0,0,0,4,1,$_REQUEST['year']);
        $end_date = mktime(0,0,0,7,1,$_REQUEST['year']);
        break;
    case 'Q3':
        $start_date = mktime(0,0,0,7,1,$_REQUEST['year']);
        $end_date = mktime(0,0,0,10,1,$_REQUEST['year']);
        break;
    case 'Q4':
        $start_date = mktime(0,0,0,10,1,$_REQUEST['year']);
        $end_date = mktime(0,0,0,1,1,$_REQUEST['year']+1);
        break;
    default:
        $start_date = mktime(0,0,0,$_REQUEST['month'],1,$_REQUEST['year']);
        $end_date = mktime(0,0,0,$_REQUEST['month']+1,1,$_REQUEST['year']);
        break;
}
$end_date--;
$date_clause = "BETWEEN '".gmdate('Y-m-d H:i:s',$start_date)."' AND '".gmdate('Y-m-d H:i:s',$end_date)."'";


$query_details_select = 
" leadaccounts.name 'Account',
  concat(leadcontacts.first_name, ' ', leadcontacts.last_name) 'Lead Name',
  leadcontacts_audit_user.user_name 'Lead Pass By',
  leadcontacts_user.user_name 'Lead Assigned To',
  leadcontacts.status 'Status',
  leadcontacts_cstm.purchasing_timeline_c 'Purchasing Timelime',
  leadcontacts_cstm.lead_group_c 'Lead Group'
";
$query_from = 
"  leadcontacts 
        inner join leadcontacts_cstm on leadcontacts.id = leadcontacts_cstm.id_c
        inner join leadcontacts_audit on leadcontacts.id = leadcontacts_audit.parent_id
        inner join users leadcontacts_audit_user on leadcontacts_audit.created_by = leadcontacts_audit_user.id
        inner join users leadcontacts_user on leadcontacts.assigned_user_id = leadcontacts_user.id
        INNER JOIN leadaccounts ON leadcontacts.leadaccount_id = leadaccounts.id 
";
$query_where = 
" leadcontacts_audit.field_name = 'lead_pass_c' AND
  leadcontacts_audit.before_value_string = 0 AND
  leadcontacts_audit.after_value_string = 1 AND
  leadcontacts_cstm.lead_pass_c = 1 AND
  leadcontacts.deleted = 0 AND
  leadcontacts_cstm.lead_group_c in ('Inside', 'Corporate', 'Enterprise', 'Partner') AND
  leadcontacts_audit.date_created $date_clause
";

$headers_summary = array('Date', 'User', 'Inside', 'Corporate', 'Enterprise', 'Partner', 'Total');
$query_summary = 
"SELECT *, count(*) 'count' FROM (".
	"SELECT '{$_REQUEST['year']}-{$_REQUEST['month']}', count(*) 'count_inner', leadcontacts_audit_user.user_name 'pass_user_name', leadcontacts_cstm.lead_group_c 'lead_group', leadcontacts_cstm.lead_pass_department_c 'department'\n".
	"FROM $query_from WHERE $query_where GROUP BY leadcontacts_audit.parent_id, leadcontacts_audit.created_by, leadcontacts_audit.before_value_string, leadcontacts_audit.after_value_string".
") foo
GROUP BY foo.pass_user_name, foo.lead_group
ORDER BY foo.department, foo.pass_user_name, foo.lead_group";

//$page_output .= $query_summary; sugar_die('');

$slave_db = &DBManagerFactory::getInstance('slave_select');

$res = $slave_db->query($query_summary,true);
if(!$res)
	sugar_die("Error 2232: Please contact <a href='mailto:sadek@sugarcrm.com'>sadek</a> with this message.");

$summary_data = array();
$display_date = "{$_POST['year']}-{$_POST['month']}";
while($row = $slave_db->fetchByAssoc($res)){
	$summary_data[$row['pass_user_name']][$row['lead_group']] = $row['count'];
}

if(!$export){
	$page_output .= "<h3>Summary (".$date_clause.")</h3><BR>";
	$page_output .= "\n<table border='0' cellpadding='0' cellspacing='0' width='100%'>\n";
	$page_output .= "<tr>\n<th class='tabDetailViewDF'>" . implode("\n</th>\n<th class='tabDetailViewDF'>\n", $headers_summary) . "</th>\n</tr>\n";
}
$sum_arr = array('Inside' => 0, 'Corporate' => 0, 'Enterprise' => 0, 'Partner' => 0);
foreach($summary_data as $user_name => $group_arr){
	$inside = isset($group_arr['Inside']) ? $group_arr['Inside'] : 0;
	$corporate = isset($group_arr['Corporate']) ? $group_arr['Corporate'] : 0;
	$enterprise = isset($group_arr['Enterprise']) ? $group_arr['Enterprise'] : 0;
	$partner = isset($group_arr['Partner']) ? $group_arr['Partner'] : 0;
	$sum = $inside + $corporate + $enterprise + $partner;
	$sum_arr['Inside'] += $inside;
	$sum_arr['Corporate'] += $corporate;
	$sum_arr['Enterprise'] += $enterprise;
	$sum_arr['Partner'] += $partner;
	if(!$export){
		$page_output .= "<tr>\n<td class='tabDetailViewDF'><center>$display_date</center></td>\n";
		$page_output .= "<td class='tabDetailViewDF'><center>$user_name</center></td>\n";
		$page_output .= "<td class='tabDetailViewDF'><center>$inside</center></td>\n";
		$page_output .= "<td class='tabDetailViewDF'><center>$corporate</center></td>\n";
		$page_output .= "<td class='tabDetailViewDF'><center>$enterprise</center></td>\n";
		$page_output .= "<td class='tabDetailViewDF'><center>$partner</center></td>\n";
		$page_output .= "<td class='tabDetailViewDF'><center><b>$sum</b></center></td>\n";
		$page_output .= "</tr>\n";
	}
}
if(!$export){
	$page_output .= "<tr>\n<th class='tabDetailViewDF'><center>Totals</center></th>\n";
	$page_output .= "<th class='tabDetailViewDF'><center>&nbsp;</center></th>\n";
	$page_output .= "<th class='tabDetailViewDF'><center>{$sum_arr['Inside']}</center></th>\n";
	$page_output .= "<th class='tabDetailViewDF'><center>{$sum_arr['Corporate']}</center></th>\n";
	$page_output .= "<th class='tabDetailViewDF'><center>{$sum_arr['Enterprise']}</center></th>\n";
	$page_output .= "<th class='tabDetailViewDF'><center>{$sum_arr['Partner']}</center></th>\n";
	$page_output .= "<th class='tabDetailViewDF'><center>&nbsp;</center></th>\n";
	$page_output .= "</table>";
	
	$page_output .= "<BR>";
}

if(!$export){
	$headers_details = array('',     'Account', 'Lead Name', 'Lead Pass By', 'Lead Assigned To', 'Status', 'Purchasing Timeline', 'Lead Group', 'Date Passed (GMT)');
}
else{
	$headers_details = array(/*'',*/ 'Account', 'Lead Name', 'Lead Pass By', 'Lead Assigned To', 'Status', 'Purchasing Timeline', 'Lead Group', 'Date Passed (GMT)', 'Date Created');
}

$query_details = "SELECT\n".
				"$query_details_select,\n  leadcontacts_audit.date_created 'Date Passed (GMT)'\n".($export ? "  ,leadcontacts.date_entered\n" : "").
				"FROM $query_from\n".
				"WHERE $query_where\n".
				"GROUP BY leadcontacts_audit.parent_id, leadcontacts_audit.created_by, leadcontacts_audit.before_value_string, leadcontacts_audit.after_value_string\n".
				"ORDER BY leadcontacts_audit_user.user_name";

//$page_output .= $query_details; sugar_die('');

$res = $slave_db->query($query_details,true);
if(!$res)
	sugar_die("Error 2233: Please contact <a href='mailto:sadek@sugarcrm.com'>sadek</a> with this message and the error below.<BR><BR>".mysql_errno($slave_db->database).": ".mysql_error($slave_db->database));


if(!$export){
	$page_output .= "<h3>Details</h3><BR>";
	$page_output .= "\n<table border='0' cellpadding='0' cellspacing='0' width='100%'>\n";
	$page_output .= "<tr>\n<th class='tabDetailViewDF'>" . implode("\n</th>\n<th class='tabDetailViewDF'>\n", $headers_details) . "</th>\n</tr>\n";
}
else{
	$page_output .= '"'.implode('","', $headers_details).'"'."\n";
}
$count = 0;
$current_rep = '';
while($row = $slave_db->fetchByAssoc($res)){
	if($row['Lead Pass By'] != $current_rep && !$export){
		if(!$export){
			$page_output .= "<tr>\n<td class='tabDetailViewDF'><b>{$row['Lead Pass By']}</b></td>";
		}
		else{
			$page_output .= '"'.$row['Lead Pass By'].'",';
		}
		for($i = 0; $i < count($row); $i++){
			if(!$export){
				$page_output .= "\n<td class='tabDetailViewDF'>&nbsp;</td>\n";
			}
			else{
				$page_output .= '"",';
			}
		}
		if(!$export){
			$page_output .= "</tr>\n";
		}
		else{
			$page_output .= "\n";
		}
		$current_rep = $row['Lead Pass By'];
		$count = 1;
	}
	if(!$export){
		array_unshift($row, $count);
		$page_output .= "<tr>\n<td class='tabDetailViewDF'>" . implode("\n</td>\n<td class='tabDetailViewDF'>\n", $row) . "</td>\n</tr>\n";
	}
	else{
		$page_output .= '"' . implode('","', $row) . '"'."\n";
	}
	$count++;
}
if(!$export){
	$page_output .= "</table>";
	
	$page_output .= "<BR>";
	$page_output .= "<a href='{$_SERVER['REQUEST_URI']}'>Go back</a>";
}
else{
	header("Pragma: cache");
	header("Content-type: application/octet-stream; charset=".$GLOBALS['locale']->getExportCharset());
	header("Content-Disposition: attachment; filename=LeadPassReport".date("Y-m-d").".csv");
	header("Content-transfer-encoding: binary");
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT" );
	header("Cache-Control: post-check=0, pre-check=0", false );
	header("Content-Length: ".strlen($page_output));	
}

echo $page_output;

?>
