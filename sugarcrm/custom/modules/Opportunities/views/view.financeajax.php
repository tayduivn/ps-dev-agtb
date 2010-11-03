<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/SugarView.php');
require_once('modules/Opportunities/Opportunity.php');
require_once('modules/Accounts/Account.php');
require_once('modules/Contacts/Contact.php');
require_once('modules/Tasks/Task.php');
require_once('modules/LeadContacts/LeadContact.php');
                
class OpportunitiesViewFinanceajax extends SugarView 
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
		$targetUpdateUsers = array(
		    'lkaji',
		    'sadek',
		);
		if(!in_array($GLOBALS['current_user']->user_name, $targetUpdateUsers)){
		    sugar_die('You do not have access to this page. Please contact <a href="mailto:internalsystems@sugarcrm.com">Internal Systems</a> if you think you should have access.');
		}
		
		if(empty($_REQUEST['year']) || empty($_REQUEST['month'])){
			sugar_die('Please set the year and month in the REQUEST data.');
		}
		
		require_once('custom/si_custom_files/FinanceReportFunctions.php');
		require_once('include/JSON.php');
		
		$return_data = array();
		global $projection_array;
		foreach($target_departments as $dept){
			$return_data['estaff'][$dept] = getTargetFor($dept, 'estaff', $_REQUEST['year'], $_REQUEST['month']);
			$return_data['board'][$dept] = getTargetFor($dept, 'board', $_REQUEST['year'], $_REQUEST['month']);
			$return_data['adjustment'][$dept] = getTargetFor($dept, 'adjustment', $_REQUEST['year'], $_REQUEST['month']);
		}
		
		$json = new JSON();
		$return = $json->encode($return_data);
		echo $return;
    }
}
?>
