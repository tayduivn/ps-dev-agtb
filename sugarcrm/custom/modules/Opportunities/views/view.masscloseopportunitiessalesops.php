<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/SugarView.php');
require_once('modules/Opportunities/Opportunity.php');
require_once('modules/Accounts/Account.php');
require_once('modules/Contacts/Contact.php');
require_once('modules/Tasks/Task.php');
require_once('modules/LeadContacts/LeadContact.php');
                
class OpportunitiesViewMasscloseopportunitiessalesops extends SugarView 
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
		// Who to assign the task to
		$assign_task_to = '';
		// The Sales Stage it must be in to show up in the list
		$before_sales_stage = '';
		// The Sales Stage it will move to if rejected
		$reject_sales_stage = '';
		// The Sales Stage it gets moved to if closed
		$after_sales_stage = '';
		// The display in the header of the script
		$header_display = '';
		if($GLOBALS['current_user']->user_name != 'sadek' && $GLOBALS['current_user']->user_name != 'rmeeker' && $GLOBALS['current_user']->user_name != 'ggallardo' && !$GLOBALS['current_user']->check_role_membership('Finance')){
		    //if(!$GLOBALS['current_user']->check_role_membership('Sales Operations')){
			sugar_die('You do not have access to this page. Please contact Internal Systems if you think you should have access.');
		}
		else{
			$assign_task_to = 'opp_owner';
			$reject_sales_stage = 'Contract';
			$before_sales_stage = 'Closed Won';
			$after_sales_stage = 'Sales Ops Closed';
			$header_display = 'Sales Ops Opportunities (currently Sales Rep Closed)';
		}
		
		echo "<h2>$header_display</h2><BR>";
		
		// Form to select the timeframe
		if(!isset($_REQUEST['year'])){
		$output =<<<EOQ
		<form method=post action="{$_SERVER['REQUEST_URI']}">
		<table border="0" cellpadding="0" cellspacing="0" width="50%">
			<tr>
			<td width="90%" class="tabDetailViewDL">
			Please select the year you would like to filter on:
			</td>
			<td width="10%" class="tabDetailViewDF">
			<select name=year>
			<option value=2007>2007</option>
			<option value=2008>2008</option>
			<option value=2009>2009</option>
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
		<input type=submit value="Run Report">
		</form>
EOQ;
			echo $output;
			die();
		}
		
		// If they have submitted the form to close the opps, we close them
		if(isset($_REQUEST['close_opps'])){
			//echo "<PRE>"; print_r($_POST); die();
			require_once('custom/si_custom_files/MassCloseOpportunitiesFunctions.php');
			require_once('modules/Opportunities/Opportunity.php');
			foreach($_POST as $key => $checkbox){
				if(strpos($key, 'oppclose') === 0){
					$opp_id = str_replace('oppclose', '', $key);
					$reason = '';
					if(!empty($_POST["oppreason$opp_id"])){
						$reason = $_POST["oppreason$opp_id"];
					}
					if($_POST[$key] == 'close'){
						closeOpportunity($opp_id, $after_sales_stage, $reason);
					}
					else if($_POST[$key] == 'reject'){
						rejectOpportunity($opp_id, $reject_sales_stage, $reason, $assign_task_to);
					}
					else if($_POST[$key] == 'reject_to_sales_rep'){
						$pre_assign_to = $assign_task_to;
						$pre_reject_sales_stage = $reject_sales_stage;
						$assign_task_to = 'opp_owner';
						$reject_sales_stage = 'Committed';
						rejectOpportunity($opp_id, $reject_sales_stage, $reason, $assign_task_to);
						$assign_task_to = $pre_assign_to;
						$reject_sales_stage = $pre_reject_sales_stage;
					}
				}
			}
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
			default:
				$date_condition = "LEFT(opportunities.date_closed, 7) = '{$_REQUEST['year']}-{$_REQUEST['month']}'";
				break;
		}
		
		$query_details_select = 
		"  accounts.id 'account_id',
		  accounts.name 'account_name',
		  accounts.account_type 'account_type',
		  opportunities.id 'id',
		  opportunities.name 'opportunity_name',
		  opportunities_cstm.opportunity_type 'opportunity_type',
		  opportunities.date_closed 'date_closed',
		  opportunities_cstm.users 'subscriptions',
		  opportunities.amount 'amount',
		  opportunities_cstm.additional_training_credits_c 'learning_credits',
		  opportunities_cstm.Term_c 'term',
		  opportunities_cstm.order_number 'order_number',
		  opportunities_cstm.order_type_c 'order_type_c',
		  opportunities_cstm.Revenue_Type_c 'revenue_type',
		  assigned_user.user_name 'assigned_user'
		";
		$query_from = 
		"  opportunities inner join opportunities_cstm on opportunities.id = opportunities_cstm.id_c
		        inner join users assigned_user on opportunities.assigned_user_id = assigned_user.id
		        inner join accounts_opportunities on opportunities.id = accounts_opportunities.opportunity_id and accounts_opportunities.deleted = 0
		        inner join accounts on accounts_opportunities.account_id = accounts.id and accounts.deleted = 0
		";
		$query_where = 
		"  opportunities.sales_stage = '$before_sales_stage' AND
		   opportunities.deleted = 0 AND
		   $date_condition
		";
		$query_order = "opportunities.date_closed";
		if(!empty($_REQUEST['order'])){
			$query_order = $_REQUEST['order'];
		}
		$query_sort = '';
		if(!empty($_REQUEST['order_sort'])){
			$query_sort = $_REQUEST['order_sort'];
		}
		
		$headers_details = array(
			'accounts.name' => 'Account',
			'accounts.account_type' => 'Account Type',
			'opportunities.name' => 'Opportunity',
			'opportunities_cstm.opportunity_type' => 'Opp Type',
			'opportunities_cstm.users' => 'Subscriptions',
			'opportunities.amount' => 'Amount',
			'opportunities.additional_training_credits_c' => 'Learning Credits',
			'opportunities_cstm.Term_c' => 'Term',
			'opportunities_cstm.Revenue_Type_c' => 'Revenue Type',
			'opportunities_cstm.order_number' => 'Order Number',
			'opportunities_cstm.order_type_c' => 'Order Type',
			'assigned_user.user_name' => 'Assigned User',
			'opportunities.date_closed' => 'Date Closed',
		);
		$headers_details['reject'] = 'Reject';
		$headers_details['close'] = 'Close';
		$headers_details['reason'] = 'Explanation';
		
		$query_details = "SELECT\n$query_details_select FROM $query_from WHERE $query_where ORDER BY $query_order $query_sort";
		
		//echo $query_details; sugar_die('');
		
		$res = $GLOBALS['db']->query($query_details);
		if(!$res)
			sugar_die("Error 2257: Please contact <a href='mailto:sadek@sugarcrm.com'>sadek</a> with this message.");
		
		$additional_uri = '';
		if(strpos($_SERVER['REQUEST_URI'], 'month=') === false){
			$additional_uri = "&year={$_REQUEST['year']}&month={$_REQUEST['month']}";
		}
		echo "<form action='{$_SERVER['REQUEST_URI']}{$additional_uri}' method=post>\n";
		echo "<input type=submit name=close_opps value='Close Opportunities'><BR>\n";
		echo '
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
		';
		echo "<tr>\n";
		$current_uri = $_SERVER['REQUEST_URI'];
		if(strpos($_SERVER['REQUEST_URI'], 'order=') !== false){
			$current_uri = preg_replace('/&order=[a-zA-Z\_\.]*/', '', $current_uri);
			$current_uri = str_replace('&order_sort=desc', '', $current_uri);
		}
		foreach($headers_details as $order_by => $head_display){
			$order_sort = '';
			if(isset($_REQUEST['order']) && $_REQUEST['order'] == $order_by && !isset($_REQUEST['order_sort'])){
				$order_sort = '&order_sort=desc';
			}
			echo "<th class='tabDetailViewDF'><a href='{$current_uri}{$additional_uri}&order={$order_by}{$order_sort}'>$head_display</a></th>\n";
		}
		echo "</tr>\n";
		while($row = $GLOBALS['db']->fetchByAssoc($res)){
			$details = array(
				"<a href=index.php?module=Accounts&action=DetailView&record={$row['account_id']} target=_blank>{$row['account_name']}</a>",
				"<center>{$row['account_type']}</center>",
				"<a href=index.php?module=Opportunities&action=DetailView&record={$row['id']} target=_blank>{$row['opportunity_name']}</a>",
				"<center>{$row['opportunity_type']}</center>",
				"<center>{$row['subscriptions']}</center>",
				"<center>"."\$".sprintf("%.2f", $row['amount'])."</center>",
				"<center>{$row['learning_credits']}</center>",
				"<center>{$row['term']}</center>",
				"<center>{$row['revenue_type']}</center>",
				"<center><a href='http://www.sugarcrm.com/sugarshop/admin/order.php?orderid={$row['order_number']}' target=_blank>{$row['order_number']}</a></center>",
				"<center>{$row['order_type_c']}</center>",			 
				"<center>{$row['assigned_user']}</center>",
				"<center>{$row['date_closed']}</center>",
			);
			$details[] = "<center><input type='radio' name='oppclose{$row['id']}' value='reject'></center>";
			$details[] = "<center><input type='radio' name='oppclose{$row['id']}' value='close'></center>";
			$details[] = "<center><input type='text' name='oppreason{$row['id']}'></center>";
			
			echo "<tr>\n<td class='tabDetailViewDF'>" . implode("\n</td>\n<td class='tabDetailViewDF'>\n", $details) . "</td>\n</tr>\n";
			unset($details);
		}
		echo "</table>\n";
		echo "<BR><input type=submit name=close_opps value='Close Opportunities'><BR>\n";
		echo "</form>\n";
		
		echo "<BR>";
		echo "<a href='{$_SERVER['REQUEST_URI']}'>Go back</a>";
    }
}
?>
