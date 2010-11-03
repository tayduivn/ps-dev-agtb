<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/SugarView.php');
require_once('modules/Opportunities/Opportunity.php');
require_once('modules/Accounts/Account.php');
require_once('modules/Contacts/Contact.php');
require_once('modules/Tasks/Task.php');
require_once('modules/LeadContacts/LeadContact.php');
                
class OpportunitiesViewFinancepipelinedetails extends SugarView 
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
		global $app_list_strings;
		
		$allowedUsers = array(
			'john',
			'steven',
			'lkaji',
			'sadek',
			'jacob',
			'gwright',
			'andy',
		);
		if(!in_array($GLOBALS['current_user']->user_name, $allowedUsers)){
			sugar_die('You do not have access to this page. Please contact <a href="mailto:internalsystems@sugarcrm.com">Internal Systems</a> if you think you should have access.');
		}
		
		$header_display = 'Finance Pipeline Details Report';
		echo "<h2>$header_display</h2><BR>";
		
		global $app_list_strings;
		global $sales_stage_order;
		$sales_stages_order = array(
			90 => 'Contract', 
			75 => 'Verbal', 
			60 => 'Negotiation', 
			50 => 'Solution', 
			25 => 'Discovery', 
			10 => 'Interested_Prospect', 
			0 => 'Closed Lost', 
		);
		
		require_once('custom/si_custom_files/custom_functions.php');
		$excluded_sales_stages = getSugarInternalClosedStages('array');
		$exclude_stages_in = "('".implode("','", $excluded_sales_stages)."')";
		$selectedyear = gmdate('Y');
		$years = array('2005', '2006', '2007', '2008', '2009', '2010');
		$sales_stage_in = "('" . implode("','", $excluded_sales_stages) . "')";
		
		// Form to select the timeframe
		if(!isset($_REQUEST['year'])){
		$output =<<<EOQ
		<form method=post action="{$_SERVER['REQUEST_URI']}">
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
			<tr>
			<td width="60%" class="tabDetailViewDL">
			Please select the year you would like to filter on:
			</td>
			<td width="40%" class="tabDetailViewDF">
			<select name=year>
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
			<select name=month>
			<option value=Q1>Q1</option>
			<option value=Q2>Q2</option>
			<option value=Q3>Q3</option>
			<option value=Q4>Q4</option>
			<option value=all>Full Year</option>
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
			<tr>
			<td width="60%" class="tabDetailViewDL">
			Please select the sales stages you'd like to see details for (those not selected will be grouped together):
			</td>
			<td width="40%" class="tabDetailViewDF">
			<select multiple name=details_sales_stages[] size=6>
EOQ;
			$sales_stage_dom = $app_list_strings['sales_stage_dom'];
			foreach($excluded_sales_stages as $s) { unset($sales_stage_dom[$s]); }
			$output .= get_select_options_with_id($sales_stage_dom, '');
			$output .=<<<EOQ
			</select>
			</td>
			</tr>
			<tr>
			<td width="60%" class="tabDetailViewDL">
			Enter the max threshold for opportunity amounts to group together (numeric value):
			</td>
			<td width="40%" class="tabDetailViewDF">
			<input name="dollar_threshold" size=20 value="5000">
			</td>
			</tr>
			<tr>
			<td width="60%" class="tabDetailViewDL">
			Remove all Closed Lost Opportunities
			</td>
			<td width="40%" class="tabDetailViewDF">
			<input type="checkbox" name="no_closed_lost" checked>
			</td>
			</tr>
		</table>
		<BR>
		<input type=submit value="Run Report">
		</form>
EOQ;
			echo $output;
			return;
		}
		
		if(empty($_REQUEST['dollar_threshold'])){
			sugar_die('Please go back and enter a maximum threshold');
		}
		
		if(empty($_REQUEST['details_sales_stages'])){
			sugar_die('Please select at least one Sales Stage from the list on the previous page.');
		}
		
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
			case 'all':
				$date_condition = "LEFT(opportunities.date_closed, 4) = '{$_REQUEST['year']}'";
				break;
			default:
				$date_condition = "LEFT(opportunities.date_closed, 7) = '{$_REQUEST['year']}-{$_REQUEST['month']}'";
				break;
		}
		
		$query_select = 
		"
		  accounts.name 'account_name',
		  accounts.id 'account_id',
		  opportunities.name 'opportunity_name',
		  opportunities.id 'opportunity_id',
		  assigned_user.user_name 'assigned_user',
		  LEFT(opportunities.date_closed, 7) 'month_closed',
		  opportunities_cstm.opportunity_type 'opportunity_type',
		  assigned_user.department 'department',
		  opportunities.sales_stage 'sales_stage',
		  opportunities.amount 'amount'
		";
		$query_from = 
		"  opportunities left join opportunities_cstm on opportunities.id = opportunities_cstm.id_c
		        inner join users assigned_user on opportunities.assigned_user_id = assigned_user.id
		        inner join accounts_opportunities on opportunities.id = accounts_opportunities.opportunity_id and accounts_opportunities.deleted = 0
		        inner join accounts on accounts_opportunities.account_id = accounts.id and accounts.deleted = 0
		";
		$query_where = 
		"   opportunities.sales_stage not in $exclude_stages_in AND
		";
		if(isset($_REQUEST['no_closed_lost']) && $_REQUEST['no_closed_lost'] == 'on'){
			$query_where .= "opportunities.sales_stage != 'Closed Lost' AND
		";
		}
		$query_where .=
		"   opportunities.deleted = 0 AND
		   $date_condition
		";
		
		$query = "SELECT\n$query_select FROM $query_from WHERE $query_where";
		
		//echo $query; sugar_die('');
		
		$res = $GLOBALS['db']->query($query);
		if(!$res)
			sugar_die("Error 2264: Please contact <a href='mailto:sadek@sugarcrm.com'>sadek</a> with this message.");
		
		function translateDate($date){
			$date_arr = explode('-', $date);
			$date_arr[0] = substr($date_arr[0], 2);
			$month_map = array('01' => 'Jan', '02' => 'Feb', '03' => 'Mar', '04' => 'Apr', '05' => 'May', '06' => 'Jun',
							   '07' => 'Jul', '08' => 'Aug', '09' => 'Sept', '10' => 'Oct', '11' => 'Nov', '12' => 'Dec');
			$date_arr[1] = $month_map[$date_arr[1]];
			return "{$date_arr[1]} '{$date_arr[0]}";
		}
		
		$department_list = array();
		$summary_array = array();
		$final_summary_by_stage = array();
		$other_sales_stage_summary = array('total' => 0);
		$final_totals_array = array('grand_total' => 0);
		//echo "<PRE>"; print_r($_REQUEST); die();
		while($row = $GLOBALS['db']->fetchByAssoc($res)){
			$department_list[$row['department']] = $row['department'];
			$row['month_closed'] = translateDate($row['month_closed']);
			if(in_array($row['sales_stage'], $_REQUEST['details_sales_stages'])){
				$summary_array[$row['sales_stage']][$row['opportunity_id']] = $row;
			}
			else{
				if(!isset($other_sales_stage_summary[$row['sales_stage']][$row['department']])){
					$other_sales_stage_summary[$row['sales_stage']][$row['department']] = $row['amount'];
					if(!isset($other_sales_stage_summary[$row['sales_stage']]['total'])){
						$other_sales_stage_summary[$row['sales_stage']]['total'] = $row['amount'];
					}
					else{
						$other_sales_stage_summary[$row['sales_stage']]['total'] += $row['amount'];
					}
				}
				else{
					$other_sales_stage_summary[$row['sales_stage']][$row['department']] += $row['amount'];
					$other_sales_stage_summary[$row['sales_stage']]['total'] += $row['amount'];
				}
				$other_sales_stage_summary['total'] += $row['amount'];
			}
		}
		sort($department_list);
		
		/*
		echo "<PRE>";
		print_r($department_list);
		print_r($summary_array);
		echo "</PRE>";
		sugar_die('');
		*/
		
		function sort_by_amount($a, $b){
			if($a['amount'] == $b['amount'])
				return 0;
			return ($a['amount'] < $b['amount']) ? 1 : -1;
		}
		
		echo "<h3>Detail Reports (By Sales Stage and Department)</h3>";
		
		global $app_list_strings;
		foreach($sales_stages_order as $sales_stage){
			if(!isset($summary_array[$sales_stage])){
				continue;
			}
			
			$s_details = $summary_array[$sales_stage];
			$totals_array = array('total' => 0);
			$below_threshold_array = array('total' => 0);
			// sort the opportunities by amount in reverse order
			usort($s_details, 'sort_by_amount');
			
			$totals_by_dept = array();
			$grand_total = 0;
			echo "<h4>{$app_list_strings['sales_stage_dom'][$sales_stage]}</h4>\n";
			echo "\n<table border='0' cellpadding='0' cellspacing='0' width='100%'>\n";
			echo "<tr>\n";
			echo "\t<th class='tabDetailViewDF'>Account Name</th>\n";
			echo "\t<th class='tabDetailViewDF'>Assigned Rep</th>\n";
			echo "\t<th class='tabDetailViewDF'>Month Close</th>\n";
			echo "\t<th class='tabDetailViewDF'>Opp Type</th>\n";
			echo "\t<th class='tabDetailViewDF'>&nbsp;</th>\n";
			foreach($department_list as $dept_name) { echo "\t<th class='tabDetailViewDF'>$dept_name</th>\n"; }
			echo "\t<th class='tabDetailViewDF'>Grand Total</th>\n";
			echo "</tr>\n";
			foreach($s_details as $opp_id => $o_details){
				// If below the threshold, add up the numbers and skip it
				if($o_details['amount'] < $_REQUEST['dollar_threshold']){
					if(!isset($below_threshold_array[$o_details['department']])){
						$below_threshold_array[$o_details['department']] = $o_details['amount'];
					}
					else{
						$below_threshold_array[$o_details['department']] += $o_details['amount'];
					}
					if(!isset($totals_array[$o_details['department']])){
						$totals_array[$o_details['department']] = $o_details['amount'];
					}
					else{
						$totals_array[$o_details['department']] += $o_details['amount'];
					}
					$below_threshold_array['total'] += $o_details['amount'];
					$totals_array['total'] += $o_details['amount'];
					continue;
				}
				
				if(!isset($totals_array[$o_details['department']])){
					$totals_array[$o_details['department']] = $o_details['amount'];
				}
				else{
					$totals_array[$o_details['department']] += $o_details['amount'];
				}
				$totals_array['total'] += $o_details['amount'];
				
				echo "<tr>\n";
				echo "\t<td class='tabDetailViewDF'><center>{$o_details['account_name']}</center></td>\n";
				echo "\t<td class='tabDetailViewDF'><center>{$o_details['assigned_user']}</center></td>\n";
				echo "\t<td class='tabDetailViewDF'><center>{$o_details['month_closed']}</center></td>\n";
				echo "\t<td class='tabDetailViewDF'><center>{$o_details['opportunity_type']}</center></td>\n";
				echo "\t<td class='tabDetailViewDF'>&nbsp;</td>\n";
				foreach($department_list as $dept){
					if($o_details['department'] != $dept){
						echo "\t<td class='tabDetailViewDF'>&nbsp;</td>\n";
					}
					else{
						echo "\t<td class='tabDetailViewDF'><center>$" . number_format($o_details['amount'], 2) . "</center></td>\n";
					}
				}
				echo "\t<td class='tabDetailViewDF'><center>$" . number_format($o_details['amount'], 2) . "</center>\n";
				echo "</tr>\n";
			}
			
			// SUMMARY ROW FOR ALL LESS THAN THE THRESHOLD
			echo "<tr bgcolor='#99ffff'>\n";
			echo "\t<th>&lt;\${$_REQUEST['dollar_threshold']} {$app_list_strings['sales_stage_dom'][$sales_stage]} Total</th>\n";
			echo "\t<td>&nbsp;</td>\n";
			echo "\t<td>&nbsp;</td>\n";
			echo "\t<td>&nbsp;</td>\n";
			echo "\t<td>&nbsp;</td>\n";
			foreach($department_list as $dept){
				echo "\t<th><center>";
				if(isset($below_threshold_array[$dept])){
					echo "$" . number_format($below_threshold_array[$dept], 2);
				}
				else{
					echo "-";
				}
				echo "</center></th>\n";
			}
			echo "\t<th><center>$" . number_format($below_threshold_array['total'], 2) . "</center></th>\n";
			echo "</tr>\n";
			
			// SUMMARY ROW FOR ALL DEALS
			echo "<tr bgcolor='#ffff99'>\n";
			echo "\t<th>{$app_list_strings['sales_stage_dom'][$sales_stage]} Total</th>\n";
			echo "\t<td>&nbsp;</td>\n";
			echo "\t<td>&nbsp;</td>\n";
			echo "\t<td>&nbsp;</td>\n";
			echo "\t<td>&nbsp;</td>\n";
			foreach($department_list as $dept){
				echo "\t<th><center>";
				if(isset($totals_array[$dept])){
					echo "$" . number_format($totals_array[$dept], 2);
				}
				else{
					echo "-";
				}
				echo "</center></th>\n";
				
				if(isset($totals_array[$dept])){
					if(!isset($final_totals_array[$dept])){
						$final_totals_array[$dept] = $totals_array[$dept];
					}
					else{
						$final_totals_array[$dept] += $totals_array[$dept];
					}
				}
			}
			echo "\t<th><center>$" . number_format($totals_array['total'], 2) . "</center></th>\n";
			echo "</tr>\n";
			
			echo "</table><BR><BR>\n";
		}
		
		// SUMMARY FOR UNSELECTED SALES STAGES
		echo "\n<table border='0' cellpadding='0' cellspacing='0' width='100%'>\n";
		echo "<tr bgcolor='#ffff99'>\n";
		echo "\t<th class='tabDetailViewDF'>&nbsp;</th>\n";
		echo "\t<th class='tabDetailViewDF'>&nbsp;</th>\n";
		echo "\t<th class='tabDetailViewDF'>&nbsp;</th>\n";
		echo "\t<th class='tabDetailViewDF'>&nbsp;</th>\n";
		echo "\t<th class='tabDetailViewDF'>&nbsp;</th>\n";
		foreach($department_list as $dept_name) { echo "\t<th class='tabDetailViewDF'>$dept_name</th>\n"; }
		echo "\t<th class='tabDetailViewDF'>Grand Total</th>\n";
		echo "</tr>\n";
		foreach($sales_stages_order as $sales_stage){
			if(in_array($sales_stage, $_REQUEST['details_sales_stages'])){
				continue;
			}
			if($sales_stage == 'Closed Lost' && isset($_REQUEST['no_closed_lost']) && $_REQUEST['no_closed_lost'] == 'on'){
				continue;
			}
			
			echo "<tr bgcolor='#ffff99'>\n";
			echo "\t<th>{$app_list_strings['sales_stage_dom'][$sales_stage]}</th>\n";
			echo "\t<th>&nbsp;</th>\n";
			echo "\t<th>&nbsp;</th>\n";
			echo "\t<th>&nbsp;</th>\n";
			echo "\t<th>&nbsp;</th>\n";
			foreach($department_list as $dept_name) {
				if(isset($other_sales_stage_summary[$sales_stage][$dept_name])){
					echo "\t<th>$" . number_format($other_sales_stage_summary[$sales_stage][$dept_name], 2) . "</th>\n";		
					if(!isset($final_totals_array[$dept_name])){
						$final_totals_array[$dept_name] = $other_sales_stage_summary[$sales_stage][$dept_name];
					}
					else{
						$final_totals_array[$dept_name] += $other_sales_stage_summary[$sales_stage][$dept_name];
					}
				}
				else{
					echo "\t<th>-</th>\n";		
				}
				
			}
			if(isset($other_sales_stage_summary[$sales_stage]['total'])){
				echo "\t<th>$" . number_format($other_sales_stage_summary[$sales_stage]['total'], 2) . "</th>\n";		
			}
			else{
				echo "\t<th>-</th>\n";		
			}
			echo "</tr>\n";
		}
		
		echo "<tr bgcolor='#ffff99'>\n";
		echo "\t<th>Grand Totals by Dept</th>\n";
		echo "\t<th>&nbsp;</th>\n";
		echo "\t<th>&nbsp;</th>\n";
		echo "\t<th>&nbsp;</th>\n";
		echo "\t<th>&nbsp;</th>\n";
		$sum = 0;
		foreach($department_list as $dept_name) {
			echo "\t<th><center>";
			if(isset($final_totals_array[$dept_name])){
				echo "$" . number_format($final_totals_array[$dept_name], 2);
				$sum += $final_totals_array[$dept_name];
			}
			else{
					echo "-";
			}
			echo "</center></th>\n";
		}
		echo "\t<th>$" . number_format($sum, 2) . "</th>\n";
		echo "</tr>\n";
		echo "</table>";
		
		echo "<a href='{$_SERVER['REQUEST_URI']}'>Go back</a>";
    }
}
?>
