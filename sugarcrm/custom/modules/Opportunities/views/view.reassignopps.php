<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/SugarView.php');
require_once('modules/Opportunities/Opportunity.php');
require_once('modules/Accounts/Account.php');
require_once('modules/Contacts/Contact.php');
require_once('modules/Tasks/Task.php');
require_once('modules/LeadContacts/LeadContact.php');
                
class OpportunitiesViewReassignopps extends SugarView 
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
		global $current_user;
		global $app_list_strings;
		
		if(!is_admin($current_user) && !$current_user->check_role_membership('Sales Operations') && !$current_user->check_role_membership('Sales Manager') && $current_user->user_name != "sadek"){
			sugar_die("You cannot access this page.");
		}
		
		?>
		<h4>Reassign Opportunities Related to Sales Rep's Accounts</h4>
		<?php
		
		if(!isset($_POST['selectuser'])){
		?>
		This will find all accounts assigned to the selected users. Then it searches for all opportunities related to those accounts, that have a sales stage and account type in the selected sales stages and account types below. At this point, it will reassign all those opportunities back to the account owner.<BR><BR>
		Please select at least one user from the selection box below:
		<form method=post action="index.php?module=Opportunities&action=reassignOpps">
		<BR>
		User's records to reassign:
		<BR>
		<select multiple=true size=5 name=selectuser[]>
		<?php
		echo get_select_options_with_id(get_user_array(FALSE), '');
		?>
		</select>
		<BR>
		<BR>
		Sales Stages to include in reassignment:
		<BR>
		<select tabindex='1' size="3" name='sales_stage[]' multiple="true">
		<?php echo get_select_options_with_id($app_list_strings['sales_stage_dom'], ''); ?>
		</select>
		<BR>
		<BR>
		Opportunity types to include in reassignment:
		<BR>
		<select tabindex='1' size="3" name='opportunity_type[]' multiple="true">
		<?php echo get_select_options_with_id($app_list_strings['opportunity_type_dom'], ''); ?>
		</select>
		<BR>
		<BR>
		Account types to include in reassignment:
		<BR>
		<select tabindex='1' size="3" name='account_type[]' multiple="true">
		<?php echo get_select_options_with_id($app_list_strings['account_type_dom'], ''); ?>
		</select>
		<BR>
		<BR>
		<input type=submit value=Submit name=steponesubmit>
		</form>
		
		<?php
		}
		else{
			if(empty($_POST['sales_stage'])){
				sugar_die("Please go back and select at least one sales stage.");
			}
			
			if(empty($_POST['opportunity_type'])){
				sugar_die("Please go back and select at least one opportunity type.");
			}
			
			if(empty($_POST['account_type'])){
				sugar_die("Please go back and select at least one account type.");
			}
			
			if(empty($_POST['selectuser'])){
				sugar_die("Please go back and select at least one user.");
			}
			
			foreach($_POST['selectuser'] as $selectuser){
				$unrow = $GLOBALS['db']->fetchByAssoc($GLOBALS['db']->query("select user_name from users where id = '$selectuser'"));
				echo "<h5>Updating for user '{$unrow['user_name']}'</h5>";
				
				$in_string_ss = "";
				foreach($_POST['sales_stage'] as $sales_stage){	
					if(empty($sales_stage))
						$in_string_ss .= "NULL, ";
					$in_string_ss .= "'$sales_stage', ";
				}
				$in_string_ss = substr($in_string_ss, 0, count($in_string_ss) - 3);
				
				$in_string_at = "";
				foreach($_POST['account_type'] as $sales_stage){
					if(empty($sales_stage))
						$in_string_at .= "NULL, ";
					$in_string_at .= "'$sales_stage', ";
				}
				$in_string_at = substr($in_string_at, 0, count($in_string_at) - 3);
				
				$in_string_ot = "";
				foreach($_POST['opportunity_type'] as $sales_stage){	
					if(empty($sales_stage))
						$in_string_ot .= "NULL, ";
					$in_string_ot .= "'$sales_stage', ";
				}
				$in_string_ot = substr($in_string_ot, 0, count($in_string_ot) - 3);
				
				$query = "select opportunities.id ".
					 "from accounts inner join accounts_opportunities on accounts.id = accounts_opportunities.account_id ".
					 	      " inner join opportunities on opportunities.id = accounts_opportunities.opportunity_id ".
					 	      " inner join opportunities_cstm on opportunities.id = opportunities_cstm.id_c ".
					 "where accounts.assigned_user_id = '$selectuser'".
					 "  and accounts.account_type in ($in_string_at)".
					 "  and opportunities_cstm.opportunity_type in ($in_string_ot)".
					 "  and opportunities.sales_stage in ($in_string_ss)";
					 "  and opportunities.deleted = 0";
					 "  and accounts_opportunities.deleted = 0";
					 "  and accounts.deleted = 0";
				
				$result = $GLOBALS['db']->query($query);
				
				require_once('modules/Opportunities/Opportunity.php');
				$successarr = array();
				$failarr = array();
				$beanarr = array();
				
				while($row = $GLOBALS['db']->fetchByAssoc($result)){
					$bean = new Opportunity();
					$bean->retrieve($row['id']);
					if(empty($bean->id)){
						continue;
					}
					if(empty($bean->name)){
						if(!empty($bean->id)){
							$failarr[] = "This Opportunity has a blank name. \"<i><a href=\"index.php?module=Opportunities&action=DetailView&record={$bean->id}\">Click here</a></i>\" to view the record.";
						}
						continue;
					}
					
					if(!empty($bean->assigned_user_id) && $bean->assigned_user_id == $selectuser){
						$successarr[] = "Opportunity \"<i><a href=\"index.php?module=Opportunities&action=DetailView&record={$bean->id}\">{$bean->name}</a></i>\" already assigned to the selected user.";
					}
					else{
						$bean->assigned_user_id=$selectuser;
						if($bean->save()){
							$successarr[] = "Successfully changed Opportunity \"<i><a href=\"index.php?module=Opportunities&action=DetailView&record={$bean->id}\">{$bean->name}</a></i>\" assignment to selected user.";
						}
						else{
							$failarr[] = "Call to \$bean->save() failed for Opportunity \"<i><a href=\"index.php?module=Opportunities&action=DetailView&record={$bean->id}\">{$bean->name}</a></i>\".";
						}
					}
				}
				
				echo "<h5>The following opportunities have been updated:</h5>";
				foreach($successarr as $ord){
					echo "$ord<BR>";
				}
				if(empty($successarr))
					echo "No opportunities updated<BR>";
				
				echo "<h5>The following opportunities could not be processed:</h5>";
				foreach($failarr as $failure){
					echo $failure."<BR>";
				}
				if(empty($failarr))
					echo "No failures<BR>";
				
			}
		
			echo "<BR><input type=button value=Return onclick='document.location=\"index.php?module=Opportunities&action=reassignOpps\"'>";
		}
    }
}
?>
