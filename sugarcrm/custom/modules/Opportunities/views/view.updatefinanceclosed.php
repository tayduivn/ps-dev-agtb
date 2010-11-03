<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/SugarView.php');
require_once('modules/Opportunities/Opportunity.php');
require_once('modules/Accounts/Account.php');
require_once('modules/Contacts/Contact.php');
require_once('modules/Tasks/Task.php');
require_once('modules/LeadContacts/LeadContact.php');
                
class OpportunitiesViewUpdatefinanceclosed extends SugarView 
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
		if(!$current_user->check_role_membership("Finance")){
			sugar_die("You cannot access this page.");
		}
		
		?>
		<h4>Update Opportunities to Sales Finance Closed</h4>
		<?php
		
		if(!isset($_POST['gofinanceclosed'])){
		?>
		Please insert a list of order numbers, one on each line:
		<form method=post action="index.php?module=Opportunities&action=updateFinanceClosed">
		<textarea name=orders cols=20 rows=20>
		</textarea>
		<BR>
		<input type=submit value=Submit name=gofinanceclosed>
		</form>
		
		<?php
		}
		else{
			require_once('modules/Opportunities/Opportunity.php');
			$validarr = array();
			$successarr = array();
			$failarr = array();
			$beanarr = array();
			$orders = explode("\n", $_POST['orders']);
			foreach($orders as $order){
				$order = trim($order);
				if(empty($order))
					continue;
				// if the order number only contains numbers
				if(preg_match("/^[0-9]+$/", $order)){
					$validarr[] = $order;
				}
				else{
					$failarr[] = "Order '$order' is not numeric. Didn't process.";
				}
			}
			
			foreach($validarr as $order_number){
				if(empty($order_number)){
					continue;
				}
				
				$oppres = $GLOBALS['db']->query("select id_c from opportunities_cstm where order_number = '$order_number'");
				if($row = $GLOBALS['db']->fetchByAssoc($oppres)){
					$bean = new Opportunity();
					$bean->retrieve($row['id_c']);
					if(empty($bean->id)){
						$failarr[] = "Could not retrieve the opportunity with order number $order_number";
					}
					if(isset($bean->sales_stage) && $bean->sales_stage == "Finance Closed"){
						$successarr[] = "Order $order_number already set to Finance Closed";
					}
					else{
						if(!isset($bean->sales_stage) || $bean->sales_stage != 'Sales Ops Closed'){
							$failarr[] = "The sales stage was not set to 'Sales Ops Closed' for order number $order_number, cannot update until this is done.";
						}
						else{
							$bean->sales_stage='Finance Closed';
							if($bean->save()){
								$successarr[] = "Successfully changed order $order_number to Finance Closed";
							}
							else{
								$failarr[] = "Call to \$bean->save() failed for order $order_number.";
							}
						}
					}
				}
				else{
					$failarr[] = "No order number $order_number found in the system.";
				}
			}
			
			echo "<h5>The opportunities with the following order numbers have been updated:</h5>";
			foreach($successarr as $ord){
				echo "$ord<BR>";
			}
			if(empty($successarr))
				echo "No valid orders entered<BR>";
			
			echo "<h5>The following order numbers could not be processed:</h5>";
			foreach($failarr as $failure){
				echo $failure."<BR>";
			}
			if(empty($failarr))
				echo "No failures<BR>";
			
			echo "<BR><input type=button value=Return onclick='document.location=\"index.php?module=Opportunities&action=updateFinanceClosed\"'>";
		}
    }
}
?>
