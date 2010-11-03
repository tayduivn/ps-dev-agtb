<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('include/MVC/View/views/view.edit.php');

class LeadAccountsViewEdit extends ViewEdit 
{
 	/**
     * Constructor
     */
 	public function __construct()
    {
 		parent::ViewEdit();
 	}
 	
 	/** 
     * @see SugarView::process()
     */
 	public function process()
    {
        parent::process();
    }
	
	public function display(){
		if($GLOBALS['current_user']->check_role_membership('Sales - Only Convert Leads')){
			echo "Error 4713: You do not have access to edit Lead records. If you feel this is in error, please file an IT Request, assigned to internalsystems, with the error number";
			return;
		}
		parent::display();
	}
}
?>
