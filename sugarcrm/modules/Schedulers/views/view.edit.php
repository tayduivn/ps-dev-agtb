<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

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

	public function __construct()
 	{
 		parent::ViewEdit();
 		$this->useForSubpanel = true;
 		//$this->useModuleQuickCreateTemplate = true;
 	}
 	
    /**
	 * @see SugarView::_getModuleTitleListParam()
	 */
	protected function _getModuleTitleListParam()
	{
	    global $mod_strings;

    	return "<a href='index.php?module=Schedulers&action=index'>".$mod_strings['LBL_MODULE_TITLE']."</a>";
    }
    

    function display(){
		global $mod_strings;
		global $app_list_strings;

		// job functions
		$this->bean->job_function = $this->bean->job;
		$this->ss->assign('JOB', $this->bean->job);
		if(substr($this->bean->job, 0, 5) == "url::") {
			$this->bean->job_url = substr($this->bean->job, 5);
			$this->ss->assign('JOB', 'url::');
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
		$this->bean->adv_interval = false;
		$this->ss->assign('basic_intervals', $ints);
		$this->ss->assign('basic_periods', $app_list_strings['scheduler_period_dom']);
		if($exInterval[0] == '*' && $exInterval[1] == '*') {
		// hours
		} elseif(strpos($exInterval[1], '*/') !== false && $exInterval[0] == '0') {
		// we have a "BASIC" type of hour setting
			$exHours = explode('/', $exInterval[1]);
			$this->ss->assign('basic_interval', $exInterval[1]);
			$this->ss->assign('basic_period', 'hour');
		// Minutes
		} elseif(strpos($exInterval[0], '*/') !== false && $exInterval[1] == '*' ) {
			// we have a "BASIC" type of min setting
			$exMins = explode('/', $exInterval[0]);
			$this->ss->assign('basic_interval', $exMins[1]);
			$this->ss->assign('basic_period', 'min');
		// we've got an advanced time setting
		} else {
			$this->ss->assign('basic_interval', 12);
			$this->ss->assign('basic_period', 'hour');
			$this->bean->adv_interval = true;
		}
		if($this->bean->time_from || $this->bean->time_to) {
			$this->bean->adv_interval = true;
		}
	
		$this->ss->assign("adv_interval", $this->bean->adv_interval?"true":"false");
		$this->ss->assign("adv_visibility", $this->bean->adv_interval?"":"display: none");
		$this->ss->assign("basic_visibility", $this->bean->adv_interval?"display: none":"");
		
		parent::display();
	}
}
