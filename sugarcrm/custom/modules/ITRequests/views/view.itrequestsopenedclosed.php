<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('custom/si_custom_files/view.openedclosedreporter.php');
require_once('modules/ITRequests/ITRequest.php');
                
class ITRequestsViewItrequestsopenedclosed extends OpenedClosedReporterView
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
		$this->closed_statuses = "= 'Closed'";
		$this->status_field = "'status'";
		parent::display();
    }
}
?>
