<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('custom/si_custom_files/view.openedclosedreporter.php');
require_once('modules/Bugs/Bug.php');
                
class BugsViewBugsopenedclosed extends OpenedClosedReporterView
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
		require_once('custom/si_custom_files/custom_functions.php');
		if(!empty($_REQUEST['start_date'])){
			$this->start_date_result = $this->getOpenBugsAtDate($_REQUEST['start_date']);
		}
		if(!empty($_REQUEST['end_date'])){
			$this->end_date_result = $this->getOpenBugsAtDate($_REQUEST['end_date']);
		}
		$this->closed_statuses = "in ".getBugClosedStatuses('in_clause');
		$this->status_field = "'status'";
		parent::display();
    }

	public function getOpenBugsAtDate($date){
		require_once('custom/si_custom_files/custom_functions.php');
		
		$bug_closed_statuses = getBugClosedStatuses();
		$bug_closed_statuses[] = 'Pending';
		
		$results = array(
			'never_closed_count' => 0,
			'closed_after' => 0,
			'last_action_reopen' => 0,
		);
		
		$result_ids = array(
		);
		
		$never_closed_outer = "
SELECT id
FROM bugs
WHERE bugs.deleted = 0 AND
      bugs.type = 'Defect' AND
      bugs.date_entered < '{$date}'
";
		
		$res = $GLOBALS['db']->query($never_closed_outer);
		
		while($row = $GLOBALS['db']->fetchByAssoc($res)){
			$never_closed_inner = "
SELECT id, date_created, before_value_string, after_value_string
FROM bugs_audit
WHERE parent_id = '{$row['id']}' AND field_name = 'status'
ORDER BY date_created ASC
";
			
			
			$res_inner = $GLOBALS['db']->query($never_closed_inner);
			$row_inner = $GLOBALS['db']->fetchByAssoc($res_inner);
			// We don't have any audit table entries
			if(!$row_inner){
				// We have to check that the bug wasn't created in closed status and never touched
				$created_as_closed = "SELECT status FROM bugs WHERE id = '{$row['id']}'";
				$cao_check_res = $GLOBALS['db']->query($created_as_closed);
				$cao_row = $GLOBALS['db']->fetchByAssoc($cao_check_res);
				if(!in_array($cao_row['status'], $bug_closed_statuses)){
					// We have scenario (A)
					$result_ids[$row['id']] = $row['id'];
					$results['never_closed_count']++;
				}
			}
			else{
				$first_action_date = null;
				$last_status = 'open';
				$before_value_string = null;
				// We do have audit table entries, iterate through them and check
				do{
					if(strtotime($row_inner['date_created']) > strtotime($date)){
						$before_value_string = $row_inner['before_value_string'];
						break;
					}
					
					if(is_null($first_action_date)){
						$first_action_date = $row_inner['date_created'];
					}
					
					if(in_array($row_inner['after_value_string'], $bug_closed_statuses)){
						$last_status = 'closed';
					}
					else{
						$last_status = 'open';
					}
				} while($row_inner = $GLOBALS['db']->fetchByAssoc($res_inner));
				
				if(is_null($first_action_date)){
					// If the first status change for this bug was after the specified date, and the before_value_string was a Closed status,
					// We know the bug was created in a closed status, and we must skip it.
					if(!in_array($before_value_string, $bug_closed_statuses)){
						$result_ids[$row['id']] = $row['id'];
						$results['closed_after']++;
					}
				}
				else if($last_status == 'open'){
					$result_ids[$row['id']] = $row['id'];
					$results['last_action_reopen']++;
				}
			}
			
		}
		
		// There are a list of audit table entries populated with the word "Array".
		// It was a bug in the past, which may skew the numbers in this script by about 100.
		// If you want to identify and clean them up, uncomment the code below to see the bug ids that need 
		//    their audit data cleaned up
		/*
		foreach($result_ids as $id){
			$query = "select status from bugs where id = '{$id}'";
			$res = $GLOBALS['db']->query($query);
			$row = $GLOBALS['db']->fetchByAssoc($res);
			if($row['status'] == 'Closed' || $row['status'] == 'Rejected'){
				echo $id."\n";
			}
		}
		*/
			
		return array_sum($results);
	}


}
?>
