<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: SchedulerOLD.php 53308 2009-12-18 17:34:04Z jmertic $
 * Description:
 ********************************************************************************/
 //FILE SUGARCRM flav=int ONLY





require_once('modules/Schedulers/language/en_us.lang.php');

class Scheduler extends SugarBean {
	// table columns
	var $id;
	var $deleted;
	var $date_entered;
	var $date_modified;
	var $modified_user_id;
	var $created_by;
	var $created_by_name;
	var $modified_by_name;
	var $name;
	var $job;
	var $date_time_start;
	var $date_time_end;
	var $job_interval;
	var $time_from;
	var $time_to;
	var $last_run;
	var $status;
	var $catch_up;
	// object attributes
	var $intervalParsed;
	var $intervalHumanReadable;
	var $metricsVar;
	var $metricsVal;
	var $dayInt;
	var $dayLabel;
	var $monthsInt;
	var $monthsLabel;
	var $suffixArray;
	var $datesArray;
	var $scheduledJobs;
	// standard SugarBean attrs
	var $table_name				= "schedulers";
	var $object_name			= "schedulers";
	var $module_dir				= "Schedulers";
	var $new_schema				= true;
	var $process_save_dates 	= true;
	var $order_by;
	// relationship
	var $scheduler_times;
//BEGIN SUGARCRM flav=int ONLY
	// monitor & daemon attrs
	var $socket;
	var $socketAddress;
	var $socketPort;
	var $socketAddressMonitor	= '127.0.0.1';
	var $socketAddressDaemon	= '127.0.0.2';
	var $socketPortMonitor		= '10000';
	var $socketPortDaemon 		= '10010';
//END SUGARCRM flav=int ONLY

	function Scheduler() {
		parent::SugarBean();
		//BEGIN SUGARCRM flav=pro ONLY
		$this->disable_row_level_security = true;
		//END SUGARCRM flav=pro ONLY
	}
	
	function handleIntervalType($type, $value, $mins, $hours) {
		global $mod_strings;
		/* [0]:min [1]:hour [2]:day of month [3]:month [4]:day of week */
		$days = array (	0 => $mod_strings['LBL_MON'],
						1 => $mod_strings['LBL_TUE'],
						2 => $mod_strings['LBL_WED'],
						3 => $mod_strings['LBL_THU'],
						4 => $mod_strings['LBL_FRI'],
						5 => $mod_strings['LBL_SAT'],
						6 => $mod_strings['LBL_SUN'],
						'*' => $mod_strings['LBL_ALL']);
		switch($type) {
			case 0: // minutes
				if($value == '0') {
					//return;
					return trim($mod_strings['LBL_ON_THE']).$mod_strings['LBL_HOUR_SING'];
				} elseif(!preg_match('/[^0-9]/', $hours) && !preg_match('/[^0-9]/', $value)) {
					return;
					
				} elseif(preg_match('/\*\//', $value)) {
					$value = str_replace('*/','',$value);
					return $value.$mod_strings['LBL_MINUTES'];
				} elseif(!preg_match('[^0-9]', $value)) {
					return $mod_strings['LBL_ON_THE'].$value.$mod_strings['LBL_MIN_MARK'];
				} else {
					return $value;
				}
			case 1: // hours
				global $current_user;
				if(preg_match('/\*\//', $value)) { // every [SOME INTERVAL] hours
					$value = str_replace('*/','',$value);
					return $value.$mod_strings['LBL_HOUR'];
				} elseif(preg_match('/[^0-9]/', $mins)) { // got a range, or multiple of mins, so we return an 'Hours' label
					return $value;
				} else {	// got a "minutes" setting, so it will be at some o'clock.
					$datef = $current_user->getUserDateTimePreferences();
					return date($datef['time'], strtotime($value.':'.str_pad($mins, 2, '0', STR_PAD_LEFT)));
				}
			case 2: // day of month
				if(preg_match('/\*/', $value)) { 
					return $value;
				} else {
					return date('jS', strtotime('December '.$value));
				}
				
			case 3: // months
				return date('F', '2005-'.$value.'-01'); 
			case 4: // days of week
				return $days[$value];
			default:
				return 'bad'; // no condition to touch this branch
		}
	}
	
	function setIntervalHumanReadable() {
		global $current_user;
		global $mod_strings;

		/* [0]:min [1]:hour [2]:day of month [3]:month [4]:day of week */
		$ints = $this->intervalParsed;
		$intVal = array('-', ',');
		$intSub = array($mod_strings['LBL_RANGE'], $mod_strings['LBL_AND']);
		$intInt = array(0 => $mod_strings['LBL_MINS'], 1 => $mod_strings['LBL_HOUR']); 
		$tempInt = '';
		$iteration = '';

		foreach($ints['raw'] as $key => $interval) {
			if($tempInt != $iteration) {
				$tempInt .= '; ';
			}
			$iteration = $tempInt;
		
			if($interval != '*' && $interval != '*/1') {
				if(false !== strpos($interval, ',')) {
					$exIndiv = explode(',', $interval);
					foreach($exIndiv as $val) {
						if(false !== strpos($val, '-')) {
							$exRange = explode('-', $val);
							foreach($exRange as $valRange) {
								if($tempInt != '') {
									$tempInt .= $mod_strings['LBL_AND'];
								}
								$tempInt .= $this->handleIntervalType($key, $valRange, $ints['raw'][0], $ints['raw'][1]);
							}
						} elseif($tempInt != $iteration) {
							$tempInt .= $mod_strings['LBL_AND'];
						}
						$tempInt .= $this->handleIntervalType($key, $val, $ints['raw'][0], $ints['raw'][1]);
					}
				} elseif(false !== strpos($interval, '-')) {
					$exRange = explode('-', $interval);
					$tempInt .= $mod_strings['LBL_FROM'];
					$check = $tempInt;
					
					foreach($exRange as $val) {
						if($tempInt == $check) {
							$tempInt .= $this->handleIntervalType($key, $val, $ints['raw'][0], $ints['raw'][1]);
							$tempInt .= $mod_strings['LBL_RANGE'];
							
						} else {
							$tempInt .= $this->handleIntervalType($key, $val, $ints['raw'][0], $ints['raw'][1]);
						}
					}

				} elseif(false !== strpos($interval, '*/')) {
					$tempInt .= $mod_strings['LBL_EVERY'];
					$tempInt .= $this->handleIntervalType($key, $interval, $ints['raw'][0], $ints['raw'][1]);
				} else {
					$tempInt .= $this->handleIntervalType($key, $interval, $ints['raw'][0], $ints['raw'][1]);
				}
			}
		} // end foreach()
		
		if($tempInt == '') {
			$this->intervalHumanReadable = $mod_strings['LBL_OFTEN'];
		} else {
			$tempInt = trim($tempInt);
			if(';' == substr($tempInt, (strlen($tempInt)-1), strlen($tempInt))) {
				$tempInt = substr($tempInt, 0, (strlen($tempInt)-1));
			}
			$this->intervalHumanReadable = $tempInt;
		}
	}
	
	
	/* take an integer and return its suffix */
	function setStandardArraysAttributes() {
		global $mod_strings;
		global $app_list_strings; // using from month _dom list
		
		$suffArr = array('','st','nd','rd');
		for($i=1; $i<32; $i++) {
			if($i > 3 && $i < 21) {
				$this->suffixArray[$i] = $i."th";	
			} elseif (substr($i,-1,1) < 4 && substr($i,-1,1) > 0) {
				$this->suffixArray[$i] = $i.$suffArr[substr($i,-1,1)];
			} else {
				$this->suffixArray[$i] = $i."th";
			}
			$this->datesArray[$i] = $i;
		}
		
		$this->dayInt = array('*',1,2,3,4,5,6,7);
		$this->dayLabel = array('*',$mod_strings['LBL_MON'],$mod_strings['LBL_TUE'],$mod_strings['LBL_WED'],$mod_strings['LBL_THU'],$mod_strings['LBL_FRI'],$mod_strings['LBL_SAT'],$mod_strings['LBL_SUN']);
		$this->monthsInt = array(0,1,2,3,4,5,6,7,8,9,10,11,12);
		$this->monthsLabel = $app_list_strings['dom_cal_month_long'];
		$this->metricsVar = array("*", "/", "-", ",");
		$this->metricsVal = array(' every ','',' thru ',' and ');
	}
	
	/* takes the serialized interval string and renders it into an array */
	function parseInterval() {
		global $metricsVar;
		$ws = array(' ', '\r','\t');
		$blanks = array('','','');
		
		$intv = $this->job_interval;
		$rawValues = explode('::', $intv);
		$rawProcessed = str_replace($ws,$blanks,$rawValues); // strip all whitespace

		$hours = $rawValues[1].':::'.$rawValues[0];
		$months = $rawValues[3].':::'.$rawValues[2];
		
		$intA = array (	'raw' => $rawProcessed,
						'hours' => $hours,
						'months' => $months,
						);
		
		$this->intervalParsed = $intA;
	}
	
	function setFieldNameMap() {
		// this silliness because we're forced to maintain 2 sets of vardefs
		global $field_name_map;
		$this->field_name_map = $this->field_defs;	
	}

	function displayCronInstructions() {
		global $mod_strings;
		global $sugar_config;
		$error = '';
		if (!isset($_SERVER['Path'])) {
            $_SERVER['Path'] = getenv('Path');
		}
		if(is_windows()) {
			if(!strpos($_SERVER['Path'], 'php')) {
				$error = '<em>'.$mod_strings['LBL_NO_PHP_CLI'].'</em>';
			}
		} else {
			if(!strpos($_SERVER['PATH'], 'php')) {
				$error = '<em>'.$mod_strings['LBL_NO_PHP_CLI'].'</em>';
			}
		}
					
		
		
		if(is_windows()) {
			echo '<br>';
			echo '
				<table cellpadding="0" cellspacing="0" width="100%" border="0" class="list view">
				<tr height="20">
					<th><slot>
						'.$mod_strings['LBL_CRON_INSTRUCTIONS_WINDOWS'].' 
					</slot></th>
				</tr>
				<tr class="evenListRowS1">
					<td scope="row" valign="top" width="70%"><slot>
						'.$mod_strings['LBL_CRON_WINDOWS_DESC'].'<br>
						<b>cd '.realpath('./').'<br>
						php.exe -f cron.php</b>
					</slot></td>
				</tr>
			</table>';
		} else {
			echo '<br>';
			echo '
				<table cellpadding="0" cellspacing="0" width="100%" border="0" class="list view">
				<tr height="20">
					<th><slot>
						'.$mod_strings['LBL_CRON_INSTRUCTIONS_LINUX'].' 
					</slot></th>
				</tr>
				<tr class="oddListRowS1">
					<td scope="row" valign="top" width="70%"><slot>
						'.$mod_strings['LBL_CRON_LINUX_DESC'].'<br>
						<b>*&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;&nbsp;*&nbsp;&nbsp;&nbsp;&nbsp;
						cd '.realpath('./').'; php -f cron.php > /dev/null 2>&1</b>
						<br>'.$error.'
					</slot></td>
				</tr>
			</table>';
		}
	}

	function checkCurl() {
		global $mod_strings;
		
		if(!function_exists('curl_init')) {
			echo '
			<table cellpadding="0" cellspacing="0" width="100%" border="0" class="list view">
				<tr height="20">
					<th width="25%" colspan="2"><slot>
						'.$mod_strings['LBL_WARN_CURL_TITLE'].' 
					</slot></th>
				</tr>
				<tr>
					<td scope="row" valign=TOP bgcolor="#fdfdfd" width="20%"><slot>
						'.$mod_strings['LBL_WARN_CURL'].'
					<td scope="row" valign=TOP class="oddListRowS1" bgcolor="#fdfdfd" width="80%"><slot>
						<span class=error>'.$mod_strings['LBL_WARN_NO_CURL'].'</span>
					</slot></td>
				</tr>
			</table>
			<br>';
		}
	}
	
	function setColumnFields() {
		global $fields_array;
		$this->column_fields = $fields_array['Schdeduler']['column_fields'];
	}

	/**
	 * function overrides the one in SugarBean.php
	 */
	function create_export_query($order_by, $where, $show_deleted = 0) {
		return $this->create_new_list_query($order_by, $where,array(),array(), $show_deleted = 0);
	}

	/**
	 * function overrides the one in SugarBean.php
	 */

	/**
	 * function overrides the one in SugarBean.php
	 */
	function fill_in_additional_list_fields() {
		$this->fill_in_additional_detail_fields();
	}

	/**
	 * function overrides the one in SugarBean.php
	 */
	function fill_in_additional_detail_fields() {
		
    }

	/**
	 * function overrides the one in SugarBean.php
	 */
	function get_list_view_data(){
		global $mod_strings;
		$temp_array = $this->get_list_view_array();
        $temp_array["ENCODED_NAME"]=$this->name;
        $this->parseInterval();
        $this->setIntervalHumanReadable();
        $temp_array['JOB_INTERVAL'] = $this->intervalHumanReadable;
        if($this->date_time_end == '2020-12-31 23:59' || $this->date_time_end == '') {
        	$temp_array['DATE_TIME_END'] = $mod_strings['LBL_PERENNIAL'];
        }
    	$this->created_by_name = get_assigned_user_name($this->created_by);
		$this->modified_by_name = get_assigned_user_name($this->modified_user_id);
    	return $temp_array;
    	
	}

	/**
	 * returns the bean name - overrides SugarBean's
	 */
	function get_summary_text() {
		return $this->name;
	}

} // end Schedulers class desc.



?>