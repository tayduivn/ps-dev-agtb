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
//FILE SUGARCRM flav=int ONLY
 



class SchedulersJob extends SugarBean {
	// schema attributes
	var $id = '';
	var $deleted = '';
	var $date_entered = '';
	var $date_modified = '';
	var $job_id = '';
	var $execute_time = '';
	var $status;
	// standard SugarBean child attrs
//	var $field_defs;
	var $table_name		= "schedulers_times";
	var $object_name	= "SchedulersJob";
	var $module_dir		= "SchedulersJobs";
	var $new_schema		= true;
	var $process_save_dates = true;
	// related fields
	var $job_name;	// the Scheduler's 'name' field
	var $job;		// the Scheduler's 'job' field
	// object specific attributes
	var $object_assigned_name; // this Job's assigned name
	var $sessId;
	var $runtime; //TODO use or not? delete this attr
	var $user; // User object
	
	/**
	 * Sole constructor.
	 */
	function SchedulersJob() {
		$GLOBALS['log']->debug('----->New Job Instantiated ['.$this->object_assigned_name.']');
		parent::SugarBean();
	}
	
	function setJobFlag($flag) {
		$status = array (0 => 'ready', 1 => 'in progress', 2 => 'completed', 3 => 'failed');
		$this->status = $status[$flag];
		$this->save();
		$this->retrieve($this->id); //TODO remove once data abstraction later is implemented.
	}
	
	/**
	 * This function takes a job_id, and updates schedulers last_run as well as
	 * soft delete the job instance from schedulers_times
	 * @param	$job_id		Id of a job in the schedulers table
	 * @return	boolean		Success
	 */
	function finishJob($job_id) {
		//BEGIN SUGARCRM flav=int ONLY
		$GLOBALS['log']->info('----->Updating Job Status and finishing Job execution.');
		//END SUGARCRM flav=int ONLY
		$qRun = 'UPDATE schedulers s SET s.last_run = '.db_convert('\''.gmdate($GLOBALS['timedate']->get_db_date_time_format(),strtotime('now')).'\'', 'datetime').' WHERE s.id = \''.$job_id.'\'';
		$this->db->query($qRun);
	}
	
	/**
	 * This function takes a job_id, gets the job, and runs it
	 * @param	$job_id		Id of a job in the schedulers table
	 * @return	boolean		Success
	 */
	function startJob($job_id) {
		require_once('modules/Schedulers/_AddJobsHere.php');
		if(empty($this->db)) {
			$this->db = DBManagerFactory::getInstance();
		}
		
		$q = 'SELECT job FROM schedulers WHERE deleted=0 AND id = \''.$job_id.'\'';
		$r = $this->db->query($q);
		$count = 0;
		while ($a = $this->db->fetchByAssoc($r)) {
			$count++;
			$exJob = explode('::', $a['job']);
			if(is_array($exJob)) {
				$this->object_assigned_name = $exJob[1];
				if($exJob[0] == 'function') {
					$GLOBALS['log']->debug('----->JOB found a job of type FUNCTION');

					$this->setJobFlag(1);
					$func = $exJob[1];
					$GLOBALS['log']->info('----->JOB firing '.$func);
					
					$res = call_user_func($func);
					if($res) {
						$this->setJobFlag(2);
					} else {
						$this->setJobFlag(3);
					}
					$this->finishJob($job_id);
					return true;
					
				} elseif($exJob[0] == 'url') {
					$GLOBALS['log']->debug('----->JOB found a job of type URL');
					$this->setJobFlag(1);

					if($this->fire($exJob[1])) {
						$this->setJobFlag(2);
					} else {
						$this->setJobFlag(3);
					}
					$this->finishJob($job_id);
					return true;
				}
			} //end if(rows);
		}
		
		if($count < 1) {
			$GLOBALS['log']->fatal('JOB FAILURE failed to retrieve any valid Jobs from schedulers table!');
			return false;
		}
		
		return false;
	}
	
	/** 
	 * This function takes a passed URL and cURLs it to fake multi-threading with another httpd instance
	 * @param	$job		String in URI-clean format
	 * @param	$timeout	Int value in secs for cURL to timeout. 30 default.
	 */
	//TODO: figure out what error is thrown when no more apache instances can be spun off
	function fire($job, $timeout=30) {
		$GLOBALS['log']->debug('----->Firing off Job ('.$job.') at '.date('Y-m-d H:i:s') .' USING TIMEOUT OF: '.$timeout);
		global $current_user;

		// cURL inits
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $job); // set url 
		curl_setopt($ch, CURLOPT_COOKIE, $this->sessId); // get the admin's PHPSESSID
		curl_setopt($ch, CURLOPT_FAILONERROR, true); // silent failure (code >300);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // do not follow location(); inits - we always use the current
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($ch, CURLOPT_DNS_USE_GLOBAL_CACHE, false);  // not thread-safe
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // return into a variable to continue program execution
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout); // never times out - bad idea?
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5); // 5 secs for connect timeout
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);  // open brand new conn
		curl_setopt($ch, CURLOPT_HEADER, true); // do not return header info with result
		curl_setopt($ch, CURLOPT_NOPROGRESS, true); // do not have progress bar
		curl_setopt($ch, CURLOPT_PORT, $_SERVER['SERVER_PORT']); // set port as reported by Server
		//TODO make the below configurable
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // most customers will not have Certificate Authority account
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // most customers will not have Certificate Authority account
		
		if(constant('PHP_VERSION') > '5.0.0') {
			curl_setopt($ch, CURLOPT_NOSIGNAL, true); // ignore any cURL signals to PHP (for multi-threading)
		}
		$result = curl_exec($ch);
		$cInfo = curl_getinfo($ch);	//url,content_type,header_size,request_size,filetime,http_code
									//ssl_verify_result,total_time,namelookup_time,connect_time
									//pretransfer_time,size_upload,size_download,speed_download,
									//speed_upload,download_content_length,upload_content_length
									//starttransfer_time,redirect_time
		curl_close($ch);

		if($cInfo['http_code'] < 400) {
			$GLOBALS['log']->debug('----->Firing was successful: ('.$job.') at '.date('Y-m-d H:i:s'));
			$GLOBALS['log']->debug('----->WTIH RESULT: '.strip_tags($result).' AND '.strip_tags(print_r($cInfo)));
			return true;
		} else {
			$GLOBALS['log']->fatal('Job errored: ('.$job.') at '.date('Y-m-d H:i:s'));
			return false;
		}
	}

	/**
	 * This function gets DB data and preps it for ListViews
	 */
	function get_list_view_data(){
		global $mod_strings;

		$temp_array = $this->get_list_view_array();
		$temp_array['JOB_NAME'] = $this->job_name;
		$temp_array['JOB']		= $this->job;

    	return $temp_array;
	}

	/** method stub for future customization
	 * 
	 */
	function fill_in_additional_list_fields() {
		$this->fill_in_additional_detail_fields();
	}

	function fill_in_additional_detail_fields() {
		// get the Job Name and Job fields from schedulers table
//		$q = "SELECT name, job FROM schedulers WHERE id = '".$this->job_id."'";
//		$result = $this->db->query($q);
//		$row = $this->db->fetchByAssoc($result);
//		$this->job_name = $row['name'];
//		$this->job = $row['job'];
//		$GLOBALS['log']->info('Assigned Name('.$this->job_name.') and Job('.$this->job.') to Job');
//		
//		$this->created_by_name = get_assigned_user_name($this->created_by);
//		$this->modified_by_name = get_assigned_user_name($this->modified_user_id);
		
    }

	/**
	 * returns the bean name - overrides SugarBean's
	 */
	function get_summary_text() {
		return $this->name;
	}

}  // end class Job 


?>
