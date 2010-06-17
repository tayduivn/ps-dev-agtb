<?php

require_once('include/MVC/View/views/view.edit.php');

class SchedulersViewEdit extends ViewEdit {
	protected static $xtDays = array(
				0 => 'MON',
				1 => 'TUE',
				2 => 'WED',
				3 => 'THU',
				4 => 'FRI',
				5 => 'SAT',
				6 => 'SUN');

	function display(){
		global $mod_strings;
		global $app_list_strings;
		include_once('modules/Schedulers/_AddJobsHere.php');

		// job functions
		$job_functions = array('url::' => 'URL');
		foreach($job_strings as $k=>$v){
			$job_functions['function::' . $v] = $mod_strings['LBL_'.strtoupper($v)];
		}
		$this->ss->assign('job_functions', $job_functions);
		if(substr($this->bean->job, 0, 5) == "url::") {
			$this->ss->assign('job_url', substr($this->bean->job, 5));
		}
		// interval
		if(!empty($this->bean->job_interval)) {
			$exInterval = explode("::", $this->bean->job_interval);
		} else {
			$exInterval = array('*','*','*','*','*');
		}
		$this->ss->assign('mins', $exInterval[0]);
		$this->ss->assign('hours', $exInterval[1]);
		$this->ss->assign('day_of_month', $exInterval[2]);
		$this->ss->assign('months', $exInterval[3]);
		$this->ss->assign('day_of_week', $exInterval[4]);

		// Handle cron weekdays
		if($exInterval[4] == '*') {
			$this->ss->assign('ALL', "CHECKED");
			foreach(self::$xtDays as $day) {
				$this->ss->assign($day, "CHECKED");
			}
		} elseif(strpos($exInterval[4], ',')) {
			// 1,2,4
			$exDays = explode(',', trim($exInterval[4]));
			foreach($exDays as $days) {
				if(strpos($days, '-')) {
					$exDaysRange = explode('-', $days);
					for($i=$exDaysRange[0]; $i<=$exDaysRange[1]; $i++) {
						$this->ss->assign(self::$xtDays[$days], "CHECKED");
					}
				} else {
					$this->ss->assign(self::$xtDays[$days], "CHECKED");
				}
			}
		} elseif(strpos($exInterval[4], '-')) {
			$exDaysRange = explode('-', $exInterval[4]);
			for($i=$exDaysRange[0]; $i<=$exDaysRange[1]; $i++) {
				$this->ss->assign(self::$xtDays[$i], "CHECKED");
			}
		} else {
			$this->ss->assign(self::$xtDays[$exInterval[4]], "CHECKED");
		}

		// Hours
		for($i=1; $i<=30; $i++) {
			$ints[$i] = $i;
		}
		$use_adv = false;
		$this->ss->assign('basic_intervals', $ints);
		$this->ss->assign('basic_periods', $app_list_strings['scheduler_period_dom']);
		if($exInterval[0] == '*' && $exInterval[1] == '*') {
		// hours
		} elseif(strpos($exInterval[1], '*/') !== false && $exInterval[0] == '0') {
		// we have a "BASIC" type of hour setting
			$exHours = explode('/', $exInterval[1]);
			$this->ss->assign('basic_interval', $exInterval[1]);
			$$this->ss->assign('basic_period', 'hour');
		// Minutes
		} elseif(strpos($exInterval[0], '*/') !== false && $exInterval[1] == '*' ) {
			// we have a "BASIC" type of min setting
			$exMins = explode('/', $exInterval[0]);
			$this->ss->assign('basic_interval', $exMins[1]);
			$$this->ss->assign('basic_period', 'min');
		// we've got an advanced time setting
		} else {
			$this->ss->assign('basic_interval', 12);
			$this->ss->assign('basic_period', 'hour');
			$use_adv = true;
		}
		$this->ss->assign('use_adv', $use_adv);
		parent::display();
	}
}