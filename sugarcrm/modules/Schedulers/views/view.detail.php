<?php
require_once('include/MVC/View/views/view.detail.php');

class SchedulersViewDetail extends ViewDetail {

 	/**
 	 * display
 	 */
 	function display()
 	{
		$this->bean->parseInterval();
		$this->bean->setIntervalHumanReadable();
		$this->ss->assign('JOB_INTERVAL', $this->bean->intervalHumanReadable);
		$this->bean->created_by_name = get_assigned_user_name($this->bean->created_by);
		$this->bean->modified_by_name = get_assigned_user_name($this->bean->modified_user_id);

 		parent::display();
 	}
}