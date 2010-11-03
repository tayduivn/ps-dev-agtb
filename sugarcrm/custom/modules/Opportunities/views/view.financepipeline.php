<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/SugarView.php');
require_once('modules/Opportunities/Opportunity.php');
require_once('modules/Accounts/Account.php');
require_once('modules/Contacts/Contact.php');
require_once('modules/Tasks/Task.php');
require_once('modules/LeadContacts/LeadContact.php');
                
class OpportunitiesViewFinancepipeline extends SugarView 
{	
    /**
     * Constructor
     */
 	public function __construct()
    {
 		parent::SugarView();
    }
    
 	/** 
     * @see SugarView::display()
     */
 	public function display()
    {

		$allowedUsers = array(
			'john',
			'steven',
			'lkaji',
			'sadek',
			'jacob',
			'gwright',
			'andy',
		);
		$targetUpdateUsers = array(
			'lkaji',
			'sadek',
		);
		if(!in_array($GLOBALS['current_user']->user_name, $allowedUsers)){
			sugar_die('You do not have access to this page. Please contact <a href="mailto:internalsystems@sugarcrm.com">Internal Systems</a> if you think you should have access.');
		}
		
		require_once('custom/si_custom_files/FinanceReportFunctions.php');
		echo '<script language="javascript" src="include/JSON.js"></script>';
		
		echo '
		<script type="text/javascript">
		function getFinanceTarget(year, quarter){
		  var xmlHttp;
		  try{
		    // Firefox, Opera 8.0+, Safari
		    xmlHttp=new XMLHttpRequest();
		  }
		  catch (e){
		    // Internet Explorer
		    try{
		      xmlHttp=new ActiveXObject("Msxml2.XMLHTTP");
		    }
		    catch (e){
		      try{
		        xmlHttp=new ActiveXObject("Microsoft.XMLHTTP");
		      }
		      catch (e){
		        alert("Your browser does not support AJAX!");
		        return false;
		      }
		    }
		  }
		  xmlHttp.onreadystatechange=function(){
		    if(xmlHttp.readyState==4){
		      //document.getElementById("estaff_Sales - Channels").value= xmlHttp.responseText;
		      var obj = JSON.parseNoSecurity(xmlHttp.responseText);
		      str = "";
		      for ( category in obj ) {
		        for ( department in obj[category] ) {
		          obj_name = category + "_" + department;
		          document.getElementById(obj_name).value = obj[category][department];
		        }
		      }
		    }
		  }
		  xmlHttp.open("GET","index.php?module=Opportunities&action=FinanceAJAX&year=" + year + "&month=" + quarter + "&to_pdf=1",true);
		  xmlHttp.send(null);
		}
		</script>
		';
		
		$header_display = 'Finance Pipeline Report';
		echo "<h2>$header_display</h2><BR>";
		
		global $projection_array;
		$projection_array = null;
		require_once('custom/si_custom_files/custom_functions.php');
		$closed_sales_stages = getSugarInternalClosedStages('array');
		$selectedyear = gmdate('Y');
		$years = array('2005', '2006', '2007', '2008', '2009', '2010');
		$sales_stage_in = "('" . implode("','", $closed_sales_stages) . "')";
		
		// Form to select the timeframe
		if(!isset($_REQUEST['year'])){
		$output =<<<EOQ
		<form method=post action="{$_SERVER['REQUEST_URI']}" name=financeform>
		<table border="0" cellpadding="0" cellspacing="0" width="80%">
			<tr>
			<td width="60%" class="tabDetailViewDL">
			Please select the year you would like to filter on:
			</td>
			<td width="40%" class="tabDetailViewDF">
			<select name=year id=year onchange="getFinanceTarget(this.options[this.selectedIndex].value, document.getElementById('month').value);">
EOQ;
		foreach($years as $theyear){
			$selected = ($theyear == $selectedyear) ? 'selected' : '';
			$output .= "\t<option value=$theyear $selected>$theyear</option>\n";
		}
		$output .=<<<EOQ
			</select>
			</td>
			</tr>
			<tr>
			<td width="60%" class="tabDetailViewDL">
			Please select the time period you would like to filter on:
			</td>
			<td width="40%" class="tabDetailViewDF">
			<select name=month id=month onchange="getFinanceTarget(document.getElementById('year').value, this.options[this.selectedIndex].value);">
			<option value=Q1>Q1</option>
			<option value=Q2>Q2</option>
			<option value=Q3>Q3</option>
			<option value=Q4>Q4</option>
			</select>
			</td>
			</tr>
EOQ;
		if(in_array($GLOBALS['current_user']->user_name, $targetUpdateUsers)){
			$output .=<<<EOQ
			<tr>
			<td width="60%" class="tabDetailViewDL">
			Enter the current quarter's target amounts for the following departments
			</td>
			<td width="40%" class="tabDetailViewDF">
			estaff&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;board&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;adjustments
			</td>
			</tr>
EOQ;
			foreach($target_departments as $target_dept){
				$estaff_target = getTargetFor($target_dept, 'estaff', $selectedyear, 'Q1');
				$board_target = getTargetFor($target_dept, 'board', $selectedyear, 'Q1');
				$adjustment = getTargetFor($target_dept, 'adjustment', $selectedyear, 'Q1');
				$output .=<<<EOQ
			<tr>
			<td width="60%" class="tabDetailViewDL">
			$target_dept
			</td>
			<td width="40%" class="tabDetailViewDF">
			<input id="estaff_$target_dept" name="estaff_$target_dept" size='10' value="$estaff_target">&nbsp;
			<input id="board_$target_dept" name="board_$target_dept" size='10' value="$board_target">
			<input id="adjustment_$target_dept" name="adjustment_$target_dept" size='10' value="$adjustment">
			</td>
			</tr>
EOQ;
			}
		}
			$output .=<<<EOQ
		</table>
		<BR>
		<input type=submit value="Run Report">
		</form>
EOQ;
			echo $output;
			return;
		}
		
		foreach($_POST as $k => $v){
			if(strpos($k, 'estaff') !== false || strpos($k, 'board') !== false || strpos($k, 'adjustment') !== false){
				$first_underscore = strpos($k, '_');
				$category = substr($k, 0, $first_underscore);
				$department = substr($k, $first_underscore + 1);
				$department = str_replace('_', ' ', $department);
				setTargetFor($department, $category, $v, $_REQUEST['year'], $_REQUEST['month']);
			}
		}
		
		$storage_dir = 'custom/si_custom_files/meta';
		$storage_file = 'financeProjections.php';
		require("$storage_dir/$storage_file");
		
		$date_condition = '';
		switch($_REQUEST['month']){
			case 'Q1':
				$date_condition = "LEFT(opportunities.date_closed, 7) in ('{$_REQUEST['year']}-01', '{$_REQUEST['year']}-02', '{$_REQUEST['year']}-03')";
				break;
			case 'Q2':
				$date_condition = "LEFT(opportunities.date_closed, 7) in ('{$_REQUEST['year']}-04', '{$_REQUEST['year']}-05', '{$_REQUEST['year']}-06')";
				break;
			case 'Q3':
				$date_condition = "LEFT(opportunities.date_closed, 7) in ('{$_REQUEST['year']}-07', '{$_REQUEST['year']}-08', '{$_REQUEST['year']}-09')";
				break;
			case 'Q4':
				$date_condition = "LEFT(opportunities.date_closed, 7) in ('{$_REQUEST['year']}-10', '{$_REQUEST['year']}-11', '{$_REQUEST['year']}-12')";
				break;
			default:
				$date_condition = "LEFT(opportunities.date_closed, 7) = '{$_REQUEST['year']}-{$_REQUEST['month']}'";
				break;
		}
		
		$query_select = 
		"
		  opportunities_cstm.opportunity_type 'opportunity_type',
		  LEFT(opportunities.date_closed, 7) 'month_closed',
		  opportunities.sales_stage 'sales_stage',
		  opportunities.amount 'amount',
		  assigned_user.department 'department'
		";
		$query_from = 
		"  opportunities left join opportunities_cstm on opportunities.id = opportunities_cstm.id_c
		        inner join users assigned_user on opportunities.assigned_user_id = assigned_user.id
		";
		$query_where = 
		"   opportunities.deleted = 0 AND
		   opportunities.sales_stage != 'Closed Lost' AND
		   $date_condition
		";
		
		$query = "SELECT\n$query_select FROM $query_from WHERE $query_where";
		
		//echo $query; sugar_die('');
		
		$res = $GLOBALS['db']->query($query);
		if(!$res)
			sugar_die("Error 2264: Please contact <a href='mailto:sadek@sugarcrm.com'>sadek</a> with this message.");
		
		$opp_type_list = array();
		$department_list = array();
		$summary_array = array();
		$final_summary_by_stage = array();
		while($row = $GLOBALS['db']->fetchByAssoc($res)){
			$opp_type_list[$row['opportunity_type']] = $row['opportunity_type'];
			$department_list[$row['department']] = $row['department'];
			$sales_stage = $row['sales_stage'];
			if(in_array($row['sales_stage'], $closed_sales_stages)){
				if(!isset($summary_array[$row['month_closed']][$row['opportunity_type']][$row['department']])){
					$summary_array[$row['month_closed']][$row['opportunity_type']][$row['department']] = $row['amount'];
				}
				else{
					$summary_array[$row['month_closed']][$row['opportunity_type']][$row['department']] += $row['amount'];
				}
				
				// Change the sales stage to lump them together for the summary below
				$sales_stage = 'Closed Won';
			}
			
			if(!isset($final_summary_by_stage[$sales_stage][$row['department']])){
				$final_summary_by_stage[$sales_stage][$row['department']] = $row['amount'];
			}
			else{
				$final_summary_by_stage[$sales_stage][$row['department']] += $row['amount'];
			}
		}
		sort($opp_type_list);
		sort($department_list);
		
		/*
		echo "<PRE>";
		print_r($opp_type_list);
		print_r($department_list);
		print_r($totals_by_dept);
		print_r($summary_array);
		print_r($final_summary_by_stage);
		echo "</PRE>";
		*/
		
		echo "<h3>Summary Reports (By Opp Type and Department)</h3>";
		foreach($summary_array as $month_closed => $m_details){
			$totals_by_dept = array();
			$grand_total = 0;
			echo "\n<table border='0' cellpadding='0' cellspacing='0' width='100%'>\n";
			echo "<tr><th class='tabDetailViewDF'>$month_closed</th>"; foreach($department_list as $irrelevant) { echo "<th class='tabDetailViewDF'>&nbsp;</th>"; } echo "<th class='tabDetailViewDF'>&nbsp;</th></tr>\n";
			echo "<tr><th class='tabDetailViewDF'>Department</th>"; foreach($department_list as $dept_name) { echo "<th class='tabDetailViewDF'>$dept_name</th>"; } echo "<th class='tabDetailViewDF'>Grand Total</th></tr>\n";
			foreach($opp_type_list as $opp_type){
				$opp_type_total = 0;
				if(!isset($m_details[$opp_type]))
					continue;
				
				echo "<tr>\n\t<td class='tabDetailViewDF'>$opp_type</td>\n";
				foreach($department_list as $dept){
					if(!isset($m_details[$opp_type][$dept])){
						echo "\t<td class='tabDetailViewDF'><center>-</center></td>\n";
						continue;
					}
					else{
						$opp_type_total += $m_details[$opp_type][$dept];
						echo "\t<td class='tabDetailViewDF'><center>$" . number_format($m_details[$opp_type][$dept], 2) . "</center></td>\n";
					}
					if(!isset($totals_by_dept[$dept])){
						$totals_by_dept[$dept] = $m_details[$opp_type][$dept];
					}
					else{
						$totals_by_dept[$dept] += $m_details[$opp_type][$dept];
					}
					$grand_total += $m_details[$opp_type][$dept];
				}
				echo "\t<td class='tabDetailViewDF'><center>$" . number_format($opp_type_total, 2) . "</center>\n";
				echo "</tr>\n";
			}
			echo "<tr bgcolor='#ffff99'>\n\t<th>Grand Total</th>\n";
			foreach($department_list as $dept){
				echo "\t<th><center>";
				if(isset($totals_by_dept[$dept])){
					echo "$" . number_format($totals_by_dept[$dept], 2);
				}
				else{
					echo "-";
				}
				echo "</center></th>\n";
			}
			echo "\t<th><center>$" . number_format($grand_total, 2) . "</center></th>\n";
			echo "</tr>\n";
			echo "</table><BR><BR>\n";
		}
		
		$sales_stages_order = array(
			100 => 'Finance Closed', 
			99 => 'Sales Ops Closed', 
			98 => 'Closed Won', 
			90 => 'Contract', 
			75 => 'Verbal', 
			60 => 'Negotiation', 
			50 => 'Solution', 
			25 => 'Discovery', 
			10 => 'Interested_Prospect', 
		);
		
		$adjustment_total = 0;
		foreach($department_list as $dept_name){
			$adjustment_total += getTargetFor($dept_name, 'adjustment', $_REQUEST['year'], $_REQUEST['month']);
		}
		
		$department_totals = array();
		echo "<h3>Summary Reports (By Sales Stage and Department)</h3>";
		echo "\n<table border='0' cellpadding='0' cellspacing='0' width='100%'>\n";
		echo "<tr>\n\t<th class='tabDetailViewDF'>&nbsp;</th>\n";
		foreach($department_list as $dept_name){
			echo "\t<th class='tabDetailViewDF'>$dept_name</th>\n";
		}
		echo "\t<th class='tabDetailViewDF'>Total</th>\n";
		echo "\t<th class='tabDetailViewDF'>Roll Up Total</th>\n";
		echo "</tr>";
		// ADJUSTMENTS
		$projection_total = 0;
		echo "<tr bgcolor='#ccffff'>\n\t<th>Adjustments</th>\n";
		foreach($department_list as $dept_name){
			if(isset($projection_array[$_REQUEST['year']][$_REQUEST['month']]['adjustment'][$dept_name])){
				echo "\t<th><center>$" . number_format($projection_array[$_REQUEST['year']][$_REQUEST['month']]['adjustment'][$dept_name], 2) . "</center></th>\n";
				$projection_total += $projection_array[$_REQUEST['year']][$_REQUEST['month']]['adjustment'][$dept_name];
			}
			else{
				echo "\t<th><center>-</center></th>\n";
			}
		}
		echo "\t<th>&nbsp;</th>\n";
		echo "\t<th><center>$" . number_format($projection_total, 2) . "</center></th>\n";
		echo "</tr>\n";
		// END ADJUSTMENTS
		$rollup_total = 0;
		foreach($sales_stages_order as $percent => $stage_name){
				if($percent == '100'){
					$percent = '98 - 100';
				}
				echo "<tr>\n\t<th class='tabDetailViewDF'>Total $stage_name ({$percent}%)</th>\n";
				$stage_total = 0;
				foreach($department_list as $dept){
					$t = 0;
					if(!empty($final_summary_by_stage[$stage_name][$dept])){
						$t = $final_summary_by_stage[$stage_name][$dept];
					}
					if(!isset($department_totals[$dept]))
						$department_totals[$dept] = $t;
					else
						$department_totals[$dept] += $t;
					
					$stage_total += $t;
					$rollup_total += $t;
					if($t != '0') echo "\t<td class='tabDetailViewDF'><center>$" . number_format($t, 2) . "</center></td>\n";
					else echo "\t<td class='tabDetailViewDF'><center>-</center></td>\n";
				}
				echo "\t<th><center>$" . number_format($stage_total, 2) . "</center></th>\n";
				echo "\t<th bgcolor='#ff9999'><center>$" . number_format($rollup_total + $adjustment_total, 2) . "</center></th>\n";
				echo "</tr>\n";
		}
		echo "<tr bgcolor='#ffff99'>\n\t<th>Total Closed & Pipeline</th>\n";
		foreach($department_list as $dept_name){
			echo "\t<th>$" . number_format($department_totals[$dept_name] + getTargetFor($dept_name, 'adjustment', $_REQUEST['year'], $_REQUEST['month']), 2) . "</th>\n";
		}
		echo "\t<th>&nbsp;</th>\n";
		echo "\t<th><center>$" . number_format($rollup_total + $adjustment_total, 2) . "</center></th>\n";
		echo "</tr>\n";
		// ESTAFF PROJECTIONS
		$projection_total = 0;
		echo "<tr bgcolor='#dddddd'>\n\t<th>estaff targets</th>\n";
		foreach($department_list as $dept_name){
			if(isset($projection_array[$_REQUEST['year']][$_REQUEST['month']]['estaff'][$dept_name])){
				echo "\t<th><center>$" . number_format($projection_array[$_REQUEST['year']][$_REQUEST['month']]['estaff'][$dept_name], 2) . "</center></th>\n";
				$projection_total += $projection_array[$_REQUEST['year']][$_REQUEST['month']]['estaff'][$dept_name];
			}
			else{
				echo "\t<th><center>-</center></th>\n";
			}
		}
		echo "\t<th>&nbsp;</th>\n";
		echo "\t<th><center>$" . number_format($projection_total, 2) . "</center></th>\n";
		echo "</tr>\n";
		// END ESTAFF PROJECTIONS
		// BOARD PROJECTIONS
		$projection_total = 0;
		echo "<tr bgcolor='#dddddd'>\n\t<th>board targets</th>\n";
		foreach($department_list as $dept_name){
			if(isset($projection_array[$_REQUEST['year']][$_REQUEST['month']]['board'][$dept_name])){
				echo "\t<th><center>$" . number_format($projection_array[$_REQUEST['year']][$_REQUEST['month']]['board'][$dept_name], 2) . "</center></th>\n";
				$projection_total += $projection_array[$_REQUEST['year']][$_REQUEST['month']]['board'][$dept_name];
			}
			else{
				echo "\t<th><center>-</center></th>\n";
			}
		}
		echo "\t<th>&nbsp;</th>\n";
		echo "\t<th><center>$" . number_format($projection_total, 2) . "</center></th>\n";
		echo "</tr>\n";
		// END BOARD PROJECTIONS
		echo "</table>\n";
		echo "<BR><BR>\n";
		
		echo "<table><tr><td bgcolor='#ff9999'>Note: Items with this background color have adjustments applied</td></tr></table><BR>\n";
		echo "<a href='{$_SERVER['REQUEST_URI']}'>Go back</a>";
    }
}
?>
