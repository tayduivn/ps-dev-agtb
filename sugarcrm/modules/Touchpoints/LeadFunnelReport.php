<?php

if(!defined('sugarEntry')) die("Not a valid entry point");

if(!isset($GLOBALS['current_user'])){
	return;
}
else if(!$GLOBALS['current_user']->check_role_membership('Lead Funnel Access')){
	echo "You are not authorized to access this Report.<BR>";
	return;
}

echo "<h2>Lead Funnel Report</h2><BR>";

require_once('include/TimeDate.php');
$timedate = new TimeDate();

$chart_funnel_cache_file = 'cache/xml/lead_funnel_chart_data'.(!empty($GLOBALS['current_user']->id) ? $GLOBALS['current_user']->id : gmdate('YmdHis')).'.xml';
$chart_figure_a_cache_file = 'cache/xml/lead_figure_a_chart_data'.(!empty($GLOBALS['current_user']->id) ? $GLOBALS['current_user']->id : gmdate('YmdHis')).'.xml';
$chart_figure_b_cache_file = 'cache/xml/lead_figure_b_chart_data'.(!empty($GLOBALS['current_user']->id) ? $GLOBALS['current_user']->id : gmdate('YmdHis')).'.xml';
 
$user_date_format = '(yyyy-mm-dd)';
$calendar_date_format = '%Y-%m-%d';
//$user_date_format = '('. $timedate->get_user_date_format().')';
//$calendar_date_format = $timedate->get_cal_date_format();

if(!isset($_POST['start_date'])){
$output =<<<EOQ
<form name=LeadFunnelForm method=post action="{$_SERVER['REQUEST_URI']}">
<table border="0" cellpadding="0" cellspacing="0" width="50%">
	<tr>
	<td width="50%" class="tabDetailViewDL">
	Please select the start date:
	</td>
	<td width="50%" class="tabDetailViewDF">
	<span sugar='slot2'><input name='start_date' onblur="parseDate(this, '$calendar_date_format');" id='startdate_jscal_field' type="text" tabindex='2' size='11' maxlength='10' value=""> <img src="themes/default/images/jscalendar.gif"  id="startdate_jscal_trigger" align="absmiddle"> <span class="dateFormat">$user_date_format</span></span sugar='slot'>
	</td>
	</tr>
	<tr>
	<td width="50%" class="tabDetailViewDL">
	Please select the end date:
	</td>
	<td width="50%" class="tabDetailViewDF">
	<span sugar='slot3'><input name='end_date' onblur="parseDate(this, '$calendar_date_format');" id='enddate_jscal_field' type="text" tabindex='2' size='11' maxlength='10' value=""> <img src="themes/default/images/jscalendar.gif"  id="enddate_jscal_trigger" align="absmiddle"> <span class="dateFormat">$user_date_format</span></span sugar='slot'>
	</td>
	</tr>
</table>
<BR>
<input type=hidden name=to_pdf value=0>
<input type=submit value=Submit>
</form>
EOQ;
	echo $output;
	?>
	<script type="text/javascript">
	Calendar.setup ({
    	inputField : "startdate_jscal_field", ifFormat : "<?php echo $calendar_date_format; ?>", showsTime : false, button : "startdate_jscal_trigger", singleClick : true, step : 1
	});
	</script>
	<script type="text/javascript">
	Calendar.setup ({
    	inputField : "enddate_jscal_field", ifFormat : "<?php echo $calendar_date_format; ?>", showsTime : false, button : "enddate_jscal_trigger", singleClick : true, step : 1
	});
	</script>
	<?php
	sugar_die('');
}
else if(empty($_POST['start_date']) || empty($_POST['end_date'])){
	sugar_die('<i>Please go back and enter a start and end date</i>');
}

$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];

require('custom/si_custom_files/meta/leadFunnelQueries.php');

$definitions_output =<<<EOQ
<h3>Stage Definitions</h3>
<table>
<tr>
<td>
<font size=1 color=red>Raw leads</font></td><td><font size=1 color=red>All leads created in given time period</font>
</td>
</tr>
<tr>
<td>
<font size=1 color=red>Leads Passed Junk Filter</font></td><td><font size=1 color=red>Filters above and excluding “junk” leads</font>
</td>
</tr>
<tr>
<td>
<font size=1 color=red>Leads Passed Child Filter</font></td><td><font size=1 color=red>Filters above and excluding “child” leads</font>
</td>
</tr>
<tr>
<td>
<font size=1 color=red>Leads Passed Channel Filter</font></td><td><font size=1 color=red>Filters above and excluding leads assigned to Leads_partner.  This user is only used for partner registered leads and leads from people interested in becoming a partner.  It does not exclude mid market leads that have been qualified and passed to the Channels team )</font>
</td>
</tr>
<tr>
<td>
<font size=1 color=red>Leads to Scrub</font></td><td><font size=1 color=red>Filters above and excluding leads assigned to Leads_Installer.  This is not the number of leads currently requiring ‘scrubbing’ it is the number of leads created in the time period that have already been scrubbed as well as those that need to be scrubbed.</font>
</td>
</tr>
<tr>
<td>
<font size=1 color=red>Leads Passed EMEA Filter</font></td><td><font size=1 color=red>Filters above and excluding leads assigned to EMEA direct sales or EMEA Channels team since these do not get qualified by Lead Qual team, they are only scrubbed.  Leads that were qualified and considered a “Lead Pass” will filter to “Leads Passed to Reps” at the bottom of the funnel.</font>
</td>
</tr>
<tr>
<td>
<font size=1 color=red>Leads to Qualify</font></td><td><font size=1 color=red>Filters above and excluding leads that are associated to existing Accounts/Contacts, or have not yet been scrubbed.  As with the “Leads to Scrub” layer, This is not the number of leads currently requiring ‘qualifying’ it is the number of leads created in the time period that have already been qualified as well as those that need to be qualified.  Leads that were qualified and considered a “Lead Pass” will filter to “Leads Passed to Reps” at the bottom of the funnel.</font>
</td>
</tr>
<tr>
<td>
<font size=1 color=red>Leads In Process</font></td><td><font size=1 color=red>Filters above and excluding leads that marked as “Recycled” or “nurture”.  This is the number of leads created in the given time period that are assigned to a Lead Qual Rep and are actively being qualified to determine if it is passable lead or needs to be nurtured.  Leads that were qualified and considered a “Lead Pass” will filter to “Leads Passed to Reps” at the bottom of the funnel.</font>
</td>
</tr>
<tr>
<td>
<font size=1 color=red>Leads Passed to Reps</font></td><td><font size=1 color=red>Leads created in the time period selected that were passed in that time period OR have been passed since then.  The date filter is based off of the Lead Created date not the Lead Passed date.  For a complete count of Leads passed during in a specific period, please see the Lead Pass Report.</font>
</td>
</tr>
</table>
EOQ;

echo $definitions_output."<BR>";

function getLeadQueryResult($key, $start_date, $end_date){
	global $leadQueryDictionary;
	if(!isset($leadQueryDictionary[$key])){
		return null;
	}
	
	if(is_array($leadQueryDictionary[$key])){
		foreach($leadQueryDictionary[$key] as $in_key => $a_query){
			$leadQueryDictionary[$key][$in_key] = str_replace("__start_date__", $start_date . " 00:00:00", $leadQueryDictionary[$key][$in_key]);
			$leadQueryDictionary[$key][$in_key] = str_replace("__end_date__", $end_date . " 00:00:00", $leadQueryDictionary[$key][$in_key]);
		}
	}
	else{
		$leadQueryDictionary[$key] = str_replace("__start_date__", $start_date . " 00:00:00", $leadQueryDictionary[$key]);
		$leadQueryDictionary[$key] = str_replace("__end_date__", $end_date . " 00:00:00", $leadQueryDictionary[$key]);
	}
	
	$resultData = array();
	$found_one = false;
	if(is_array($leadQueryDictionary[$key])){
		$total_count = 0;
		foreach($leadQueryDictionary[$key] as $in_key => $a_query){
			$res = $GLOBALS['db']->query($a_query);
			if(!$res){
				echo "Error in getLeadQueryResult for key $key and inner index $in_key<BR>";
				echo $leadQueryDictionary[$key]."<BR>";
				echo mysql_errno($GLOBALS['db']->database) . ": " . mysql_error($GLOBALS['db']->database)."<BR>";
				return null;
			}
			while($row = $GLOBALS['db']->fetchByAssoc($res)){
				$found_one = true;
				$total_count += $row['count'];
			}
			
			if(!$found_one){
				/*
				echo "No results in getLeadQueryResult for key $key and inner index $in_key<BR>";
				echo $leadQueryDictionary[$key]."<BR>";
				*/
				return null;
			}
		}
		$resultData[] = array('count' => $total_count);
	}
	else{
		$res = $GLOBALS['db']->query($leadQueryDictionary[$key]);
		if(!$res){
			echo "Error in getLeadQueryResult for key $key<BR>";
			echo $leadQueryDictionary[$key]."<BR>";
			echo mysql_errno($GLOBALS['db']->database) . ": " . mysql_error($GLOBALS['db']->database)."<BR>";
			return null;
		}
		while($row = $GLOBALS['db']->fetchByAssoc($res)){
			$found_one = true;
			$resultData[] = $row;
		}
		
		if(!$found_one){
			/*
			echo "No results in getLeadQueryResult for key $key<BR>";
			echo $leadQueryDictionary[$key]."<BR>";
			*/
			return null;
		}
	}
	
	return $resultData;
}

function getLeadChart($data, $title, $cache_file, $chart_type = 'pie chart'){
	require_once('include/SugarCharts/SugarChart.php');
	$chart_content = '<?xml version="1.0" encoding="UTF-8"?>
<sugarcharts version="1.0">
        <properties>
                <gauge_target_list>Array</gauge_target_list>
                <title>'.$title.'</title>
                <subtitle></subtitle>
                <type>'.$chart_type.'</type>
                <legend>on</legend>
                <labels>value</labels>
                <print>on</print>
        </properties>
        <data>
';
	$first_value = 19831983;
	$last_value = 0;
	foreach($data as $label => $number){
		if($first_value == 19831983)
			$first_value = $number;
		$chart_content .= "
                <group>
                        <title>$label</title>
                        <value>$number</value>
                        <label>$number</label>
                        <subgroups>
                        </subgroups>
                </group>
";
		$last_value = $number * 1.25;
	}
	$chart_content .= 
"        <data>
        <yAxis>
                <yMin>$last_value</yMin>
                <yMax>$first_value</yMax>
                <yStep>1000</yStep>
                <yLog>1</yLog>
        </yAxis>
</sugarcharts>
";
	file_put_contents($cache_file, $chart_content);

	$sugarChart = new SugarChart();
	$chart_display_result = $sugarChart->display($cache_file, $cache_file, 380);
	return $chart_display_result;
}

$results['raw_leads'] = getLeadQueryResult('raw_leads', $start_date, $end_date);
$results['junk_filter'] = getLeadQueryResult('junk_filter', $start_date, $end_date);
$results['child_filter'] = getLeadQueryResult('child_filter', $start_date, $end_date);
$results['channel_filter'] = getLeadQueryResult('channel_filter', $start_date, $end_date);
$results['installer_no_scrub'] = getLeadQueryResult('installer_no_scrub', $start_date, $end_date);
$results['emea'] = getLeadQueryResult('emea', $start_date, $end_date);
$results['converted_existing_new'] = getLeadQueryResult('converted_existing_new', $start_date, $end_date);
$results['nurture'] = getLeadQueryResult('nurture', $start_date, $end_date);
$results['leads_active'] = getLeadQueryResult('leads_active', $start_date, $end_date);
$results['funnel_backlog'] = getLeadQueryResult('funnel_backlog', $start_date, $end_date);
$results['funnel_validation'] = getLeadQueryResult('funnel_validation', $start_date, $end_date);
//$results['lead_pass_report'] = getLeadQueryResult('lead_pass_report', $start_date, $end_date);

$raw_leads = $results['raw_leads'][0]['count'];
$junk_filter = $results['junk_filter'][0]['count'];
$child_filter = $results['child_filter'][0]['count'];
$channel_filter = $results['channel_filter'][0]['count'];
$installer_no_scrub = $results['installer_no_scrub'][0]['count'];
$emea = $results['emea'][0]['count'];
$converted_existing_new = $results['converted_existing_new'][0]['count'];
$nurture = $results['nurture'][0]['count'];
$leads_active = $results['leads_active'][0]['count'];
$funnel_backlog = $results['funnel_backlog'][0]['count'];
$funnel_validation = $results['funnel_validation'][0]['count'];

$funnel_raw_leads = $raw_leads;
$funnel_junk_filter = $funnel_raw_leads - $junk_filter;
$funnel_child_filter = $funnel_junk_filter - $child_filter;
$funnel_channel_filter = $funnel_child_filter - $channel_filter;
$funnel_installer_no_scrub = $funnel_channel_filter - $installer_no_scrub;
$funnel_emea = $funnel_installer_no_scrub - $emea;
$funnel_converted_existing_new = $funnel_emea - $converted_existing_new;
$funnel_nurture = $funnel_converted_existing_new - $nurture;
$funnel_leads_active = $funnel_nurture - $leads_active;
$funnel_funnel_backlog = $funnel_leads_active - $funnel_backlog;
$funnel_funnel_validation = $funnel_funnel_backlog - $funnel_validation;

$funnel_data = array(
	'Raw Leads' => $funnel_raw_leads,
	'Leads Passed Junk Filter' => $funnel_junk_filter,
	'Leads Passed Child Filter' => $funnel_child_filter,
	'Leads Passed Channel Filter' => $funnel_channel_filter,
	'Leads to Scrub' => $funnel_installer_no_scrub,
	'Leads Passed EMEA Filter' => $funnel_emea,
	'Leads to Qualify' => $funnel_converted_existing_new,
	'Leads in Process (Qual Rep)' => $funnel_nurture,
	'Leads Passed to Sales Reps' => $funnel_leads_active,
);

echo "<h3>Lead Funnel Data</h3><BR>\n";
echo "\n<table border='0' cellpadding='0' cellspacing='0' width='75%'>\n";
foreach($funnel_data as $label => $number){
	echo "<tr>\n";
	echo "<td class='tabDetailViewDF'>$label</td>\n";
	echo "<td class='tabDetailViewDF'>$number</td>\n";
	echo "</tr>\n";
}
echo "\n</table>\n";

echo "<BR><BR>";
echo "<table>\n";
echo "<tr>\n";
echo "<td>\n";
echo getLeadChart($funnel_data, 'Lead Funnel', $chart_funnel_cache_file, 'funnel chart 3D');
echo "</td>\n";

/*
$figure_a_data = array(
	'Total Scrub Backlog' => $unscrubbed_total,
	'Recycled' => $recycled,
	'Nurture' => $nurture,
	'Dead' => $dead_junk,
	'EMEA' => $scrubbed_to_emea,
);

echo "<td>\n";
echo getLeadChart($figure_a_data, 'Backlog & Leads Removed by Scrubbing', $chart_figure_a_cache_file);
echo "</td>\n";

$figure_b_data = array(
	'Qual Queue/Backlog' => $qual_backlog_total,
);
foreach($recycled_junk_array as $status => $value){
	$figure_b_data[$status] = $value;
}
echo "</tr>\n";
echo "<tr>\n";
echo "<td>\n";
echo getLeadChart($figure_b_data, 'Qualification Breakdown', $chart_figure_b_cache_file);
echo "</td>\n";
echo "<td>\n";
echo "</td>\n";
echo "</tr>\n";
echo "</table>\n";
*/
