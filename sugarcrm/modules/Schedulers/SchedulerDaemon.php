<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */



class SchedulerDaemon extends SugarBean
{
	// schema columns
	var $id;
	var $deleted;
	var $date_entered;
	var $date_modified;
	var $job_id;
	var $execute_time;
	// replicated SugarBean attributes
	var $db;
	// object attributes
	var $object_name = 'SchedulerDaemon';
	var $table_name = 'schedulers_times';
	var $job_array;
	var $watch_name; // this object's watch name
	var $sleepInterval = 5; // how long to sleep before checking on jobs
	var $lifespan = 45; // 2 mins to kill off this object
	var $sessId; // admin PHPSESSID
	var $runAsUserName; // admin user
	var $runAsUserPassword; // admin pword
	var $stop = false;
	var $uptimeMonitor;
	var $shutdown = false;


	/**
	 * Sole constructor.
	 */
    public function __construct()
    {
		if(empty($this->db)) {
			
			$this->db = DBManagerFactory::getInstance();
		}
			
		$GLOBALS['log']->debug('New Scheduler Instantiated.....................................');
	}
	


//BEGIN SUGARCRM flav=int ONLY
	/** 
	 * This function sends a message to the Monitor
	 * @param	$msg	Message to send to Monitor
	 * @return	boolean	Success
	 */
	function sendMsg($msg) {
		$GLOBALS['log']->debug('----->Daemon sending ['.trim($msg).'] to Monitor');
		$s = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);

		if(socket_connect($s, $this->socketAddressMonitor, $this->socketPortMonitor)) {
			if(socket_write($s, $msg, strlen($msg))) {
				$GLOBALS['log']->debug('----->Daemon sent message to Monitor');
				usleep(100);
				$GLOBALS['log']->debug('----->Daemon closing outbound port');
				socket_shutdown($s, 2);
				socket_close($s);
				return true;
			} else {
				$GLOBALS['log']->debug('----->Daemon FAILURE could not socket_send() message to Monitor');
				usleep(100);
				$GLOBALS['log']->debug('----->Daemon closing outbound port');
				socket_shutdown($s, 2);
				socket_close($s);
				return false;
			}
		} else {
			$GLOBALS['log']->debug('----->Daemon FAILURE could not socket_connect() to Monitor');
			usleep(100);
			$GLOBALS['log']->debug('----->Daemon closing outbound port');
			socket_shutdown($s, 2);
			socket_close($s);
			return false; 
		}
	}


	/**
	 * This function starts the Scheduler
	 */
	function startMonitor() {
		//require_once('config.php');
		if(empty($sugar_config)) {
			
		}
		global $sugar_config;
		$GLOBALS['log']->debug('----->Daemon starting new Monitor thread');
		$job = new Job();
		$job->object_assigned_name = 'SchedulerDaemon';
		
		if($job->fire($sugar_config['site_url'].'/index.php?entryPoint=schedulers', 1)) {
			return true;
		} else {
			return false;
		}
	}
			
	/**
	 * This function checks the state of another controller, then returns state.
	 * @param	$controller	A controller object to monitor to keep alive
	 */
	function watch() {
        global $timedate;
		$GLOBALS['log']->debug('----->SchedulerDaemon Object running as user: ('.$this->runAsUserName.')');
		$GLOBALS['log']->debug('----->SchedulerDaemon Object created '.$timedate->nowDb());

		$sleepTil = strtotime('now +'.$this->sleepInterval.'secs');
		$GLOBALS['log']->debug('----->sleepTil: '.$timedate->asDbTime($timedate->fromTimestamp($sleepTil)).' :: timerstarted at '.$timedate->nowDbTime());
		
		while($this->stop == false) {
			usleep(250); // sleep 0.25 secs
			
			if($timedate->nowDbTime() == '00:00:01') { // refresh at Midnight local time
				$GLOBALS['log']->fatal('----->Daemon refreshing job queue.');
				$this->dropSchedules();
				$this->insertSchedules();	
			}
			
			
			if(strtotime('now') >= $sleepTil) {
				if($this->checkPendingJobs()) { // now check for pending jobs
					$GLOBALS['log']->debug('----->Daemon successfully polled jobs.');
				} else {
					$GLOBALS['log']->fatal('Daemon FAILURE could not poll jobs!');
				}
				
				if(!$this->shutdown) {
					if(!$this->sendMsg("heartbeat\n")) {
						$GLOBALS['log']->fatal('Daemon FAILURE could not send heartbeat to Monitor. Monitor dead?');
	
						if($this->startMonitor()) {
							$GLOBALS['log']->fatal('----->Daemon re/started Monitor.');
							$this->checkCount = 0;
						} else {
							$GLOBALS['log']->fatal('Daemon FAILURE could not restart Monitor!');
							if($this->checkCount >= $this->maxRetry) {
								$GLOBALS['log']->fatal('Daemon FAILURE could not PING Daemon '.$this->maxRetry.' times.  Ceasing all attempts.');
								$this->shutdown = true;
							}
						}
					} else {
						$this->checkCount = 0;
					}
					$sleepTil = strtotime('+'.$this->sleepInterval.'secs');
				} else {
					$GLOBALS['log']->debug('Daemon FAILURE lost contact with Monitor! Sending admin alert email.');
					$this->sendAdminAlert('Daemon lost contact with the Monitor at GMT: '.$timedate->nowDb());
				}
			}
				
			if(($sm = @socket_accept($this->socket)) < 0) {
				$GLOBALS['log']->fatal('----->Daemon FAILURE could not accept connection on socket.');
				break;
			}
			
			if(is_resource($sm)) {  // if we get a connect resource, go ahead and say hello
				$msg = "connected!\n";
				socket_write($sm, $msg, strlen($msg));
				
				while($this->stop == false) {
					if(!is_resource($sm)) {
						break;
					}
				
					if(($buf = socket_read($sm, 2048, PHP_NORMAL_READ)) !== false) {
				
						if(	(socket_last_error($sm) == 104) ||
							(socket_last_error($sm) == 154)
						) {  // "unable to read from socket"
							$GLOBALS['log']->fatal('----->Daemon lost socket connection!  Sending SOS and killing self!');
							// send help me msg
							$this->sendMsg("help\n");
							usleep(200);
							@socket_shutdown($sm, 2);
							@socket_shutdown($this->socket, 2);
							@socket_close($sm);  // close both, 1 or both may already be dead, so do it silently
							@socket_close($this->socket);
							//$this = null; // kill myself
						}
						
						if(!$buf = trim($buf)) { // check for null, socket_read() on nothing is '0'
							continue;
						}
						
						if($buf == 'ping') {
							$GLOBALS['log']->debug('----->Daemon got PING from Monitor');
							usleep(100);
							break;
						}
						
						if($buf == 'help') {
							$GLOBALS['log']->debug('----->Daemon got HELP request message!  Attempting to restart Monitor');
							usleep(100);
							if($this->startMonitor()) {
								$msg .= "\n\n[ Scheduler Monitor has been RESTARTED ]\n";
							} else {
								$msg .= "\n\n[ Scheduler Monitor could not be RESTARTED - please check the logs for the error. ]\n";
							}
							usleep(100);
							break;
						}

						if($buf == 'die') {
							$GLOBALS['log']->debug('----->Daemon got DIE');
							$msg = "ack\n";
							socket_write($sm, $msg, strlen($msg));
							$this->stop = true;
							usleep(500);
							$GLOBALS['log']->debug('----->Daemon now suiciding.');
							break(2);	
						}
					} elseif(socket_last_error($sm) == 104) {  // "unable to read from socket"
						$GLOBALS['log']->fatal('----->Daemon lost socket connection!  Sending SOS and killing self!');
						// send help me msg
						$this->sendMsg("help\n");
						sleep(1);
						@socket_shutdown($sm, 2);
						@socket_shutdown($this->socket, 2);
						@socket_close($sm);  // close both, 1 or both may already be dead, so do it silently
						@socket_close($this->socket);
						//$this = null; // kill myself
					}
				} // inner while
			} 
			
			if(!is_bool($sm) && !is_null($sm)) { // if not connected, socket_accept = false
				socket_shutdown($sm, 2);
				socket_close($sm);
			}

			if(is_null($sm)) {
				$GLOBALS['log']->fatal('----->Daemon lost socket connection!  Sending SOS and killing self!');
				// send help me msg
				$this->sendMsg("help\n");
				sleep(1);
				@socket_shutdown($this->socket, 2);
				@socket_close($this->socket);
				//$this = null; // kill myself
			}
		} // outer while
		socket_shutdown($this->socket, 2);
		socket_close($this->socket);
	}
//END SUGARCRM flav=int ONLY



	
	/** 
	 * This function takes a look at the schedulers_times table and pulls the
	 * jobs to be run at this moment in time (anything not run and with a run
	 * time or earlier than right now)
	 * @return	$successful	Boolean flag whether a job(s) is found
	 */
	function checkPendingJobs() {
		global $sugar_config;
		global $timedate;
		
		$GLOBALS['log']->debug('');
		$GLOBALS['log']->debug('----->Scheduler checking for qualified jobs to run.');
		if(empty($this->db)) {
			$this->db = DBManagerFactory::getInstance();
		}
		
		$fireTimeMinus = $timedate->asDb($timedate->getNow()->get('-1 minute'));
		$fireTimePlus = $timedate->asDb($timedate->getNow()->get('+1 minute'));

		// collapse list of schedulers where "catch_up" is 0 and status is "ready" (not "in progress, completed, etc.");
		$q = 'UPDATE schedulers_times st
					SET st.status = \'not run\' 
					WHERE st.execute_time < '.$this->db->convert($this->db->quoted($fireTimeMinus), 'datetime').'
					AND st.status = \'ready\' 
					AND st.scheduler_id IN (SELECT s.id FROM schedulers s WHERE st.scheduler_id = s.id AND s.catch_up = 0)';
		$this->db->query($q);
		
		$q = 'SELECT DISTINCT st.id, st.scheduler_id, st.status, s.name, s.job FROM schedulers_times st
		    LEFT JOIN schedulers s ON st.scheduler_id = s.id WHERE st.execute_time < '.$this->db->convert($this->db->quoted($fireTimeMinus), 'datetime').
            ' AND st.deleted=0 AND s.deleted=0 AND st.status=\'ready\' AND s.status=\'Active\' ORDER BY s.name';
		$r = $this->db->query($q);
		$count = 0;

		while($a = $this->db->fetchByAssoc($r)) {
			
			$job = BeanFactory::newBean('SchedulersJobs');
			
			$paramJob = $a['scheduler_id'];
			$job->fire($sugar_config['site_url'].'/index.php?entryPoint=schedulers&type=job&job_id='.$paramJob.'&record='.$a['id']);
			$count++;
		}
		//BEGIN SUGARCRM flav=int ONLY
		$GLOBALS['log']->debug('----->Scheduler found '.$count.' jobs to fire!');
		//END SUGARCRM flav=int ONLY


		if($count < 1) {
			$GLOBALS['log']->debug('----->Scheduler has found 0 Jobs to fire');
		}
	}
	
	/**
	 * This function takes a Scheduler object and uses its job_interval
	 * attribute to derive DB-standard datetime strings, as many as are
	 * qualified by its ranges.  The times are from the time of calling the
	 * script.
	 * 
	 * @param	$focus		Scheduler object
	 * @return	$dateTimes	array loaded with DB datetime strings derived from
	 * 						the	 job_interval attribute
	 * @return	false		If we the Scheduler is not in scope, return false.
	 */
	function deriveDBDateTimes($focus) {
        global $timedate;
		$GLOBALS['log']->debug('deriveDBDateTimes got an object of type: '.$focus->object_name);
		/* [min][hr][dates][mon][days] */
		$dateTimes = array();
		$ints	= explode('::', str_replace(' ','',$focus->job_interval));
		$days	= $ints[4];
		$mons	= $ints[3];
		$dates	= $ints[2];
		$hrs	= $ints[1];
		$mins	= $ints[0];
		$today	= getdate($timedate->getNow()->ts);

		
		// derive day part
		if($days == '*') {
			$GLOBALS['log']->debug('got * day');

		} elseif(strstr($days, '*/')) {
			// the "*/x" format is nonsensical for this field
			// do basically nothing.
			$theDay = str_replace('*/','',$days);
			$dayName[] = str_replace($focus->dayInt, $focus->dayLabel, $theDay);
		} elseif($days != '*') { // got particular day(s)
			if(strstr($days, ',')) {
				$exDays = explode(',',$days);
				foreach($exDays as $k1 => $dayGroup) {
					if(strstr($dayGroup,'-')) {
						$exDayGroup = explode('-', $dayGroup); // build up range and iterate through
						for($i=$exDayGroup[0];$i<=$exDayGroup[1];$i++) {
							$dayName[] = str_replace($focus->dayInt, $focus->dayLabel, $i);
						}
					} else { // individuals
						$dayName[] = str_replace($focus->dayInt, $focus->dayLabel, $dayGroup);
					}
				}
			} elseif(strstr($days, '-')) {
				$exDayGroup = explode('-', $days); // build up range and iterate through
				for($i=$exDayGroup[0];$i<=$exDayGroup[1];$i++) {
					$dayName[] = str_replace($focus->dayInt, $focus->dayLabel, $i);
				}
			} else {
				$dayName[] = str_replace($focus->dayInt, $focus->dayLabel, $days);
			}
			
			// check the day to be in scope:
			if(!in_array($today['weekday'], $dayName)) {
				return false;
			}
		} else {
			return false;
		}
		
		
		// derive months part
		if($mons == '*') {
			$GLOBALS['log']->debug('got * months');
		} elseif(strstr($mons, '*/')) {
			$mult = str_replace('*/','',$mons);
			$startMon = $timedate->fromTimestamp($focus->date_time_start)->month;
			$startFrom = ($startMon % $mult);

			for($i=$startFrom;$i<=12;$i+$mult) {
				$compMons[] = $i+$mult;
				$i += $mult;
			}
			// this month is not in one of the multiplier months
			if(!in_array($today['mon'],$compMons)) {
				return false;	
			}
		} elseif($mons != '*') {
			if(strstr($mons,',')) { // we have particular (groups) of months
				$exMons = explode(',',$mons);
				foreach($exMons as $k1 => $monGroup) {
					if(strstr($monGroup, '-')) { // we have a range of months
						$exMonGroup = explode('-',$monGroup);
						for($i=$exMonGroup[0];$i<=$exMonGroup[1];$i++) {
							$monName[] = $i;
						}
					} else {
						$monName[] = $monGroup;
					}
				}
			} elseif(strstr($mons, '-')) {
				$exMonGroup = explode('-', $mons);
				for($i=$exMonGroup[0];$i<=$exMonGroup[1];$i++) {
					$monName[] = $i;
				}
			} else { // one particular month
				$monName[] = $mons;
			}
			
			// check that particular months are in scope
			if(!in_array($today['mon'], $monName)) {
				return false;
			}
		}

		// derive dates part
		if($dates == '*') {
			$GLOBALS['log']->debug('got * dates');
		} elseif(strstr($dates, '*/')) {
			$mult = str_replace('*/','',$dates);
			$startDate = $timedate->fromTimestamp($focus->date_time_start)->day;
			$startFrom = ($startDate % $mult);

			for($i=$startFrom; $i<=31; $i+$mult) {
				$dateName[] = str_pad(($i+$mult),2,'0',STR_PAD_LEFT);
				$i += $mult;
			}
			
			if(!in_array($today['mday'], $dateName)) {
				return false;	
			}
		} elseif($dates != '*') {
			if(strstr($dates, ',')) {
				$exDates = explode(',', $dates);
				foreach($exDates as $k1 => $dateGroup) {
					if(strstr($dateGroup, '-')) {
						$exDateGroup = explode('-', $dateGroup);
						for($i=$exDateGroup[0];$i<=$exDateGroup[1];$i++) {
							$dateName[] = $i; 
						}
					} else {
						$dateName[] = $dateGroup;
					}
				}
			} elseif(strstr($dates, '-')) {
				$exDateGroup = explode('-', $dates);
				for($i=$exDateGroup[0];$i<=$exDateGroup[1];$i++) {
					$dateName[] = $i; 
				}
			} else {
				$dateName[] = $dates;
			}
			
			// check that dates are in scope
			if(!in_array($today['mday'], $dateName)) {
				return false;
			}
		}
		
		// derive hours part
		//$startHour = date('G', strtotime($focus->date_time_start));
		//$currentHour = ($startHour < 1) ? 23 : date('G', strtotime($focus->date_time_start));
		$currentHour = $timedate->getNow()->hour;
		if($hrs == '*') {
			$GLOBALS['log']->debug('got * hours');
			for($i=0;$i<=24; $i++) {
				if($currentHour + $i > 23) {
					$hrName[] = $currentHour + $i - 24;
				} else {
					$hrName[] = $currentHour + $i;
				}
			}
		} elseif(strstr($hrs, '*/')) {
			$mult = str_replace('*/','',$hrs);
			for($i=0; $i<24; $i) { // weird, i know
				if($currentHour + $i > 23) {
					$hrName[] = $currentHour + $i - 24;
				} else {
					$hrName[] = $currentHour + $i;
				}
				$i += $mult;
			}
		} elseif($hrs != '*') {
			if(strstr($hrs, ',')) {
				$exHrs = explode(',',$hrs);
				foreach($exHrs as $k1 => $hrGroup) {
					if(strstr($hrGroup, '-')) {
						$exHrGroup = explode('-', $hrGroup);
						for($i=$exHrGroup[0];$i<=$exHrGroup[1];$i++) {
							$hrName[] = $i;
						}
					} else {
						$hrName[] = $hrGroup;
					}
				}
			} elseif(strstr($hrs, '-')) {
				$exHrs = explode('-', $hrs);
				for($i=$exHrs[0];$i<=$exHrs[1];$i++) {
					$hrName[] = $i;
				}
			} else {
				$hrName[] = $hrs;
			}
		}
		// derive minutes
		$currentMin = $timedate->getNow()->minute;
		if(substr($currentMin, 0, 1) == '0') {
			$currentMin = substr($currentMin, 1, 1);
		}
		if($mins == '*') {
			$GLOBALS['log']->debug('got * mins');
			for($i=0; $i<60; $i++) {
				if(($currentMin + $i) > 59) {
					$minName[] = ($i + $currentMin - 60);
				} else {
					$minName[] = ($i+$currentMin);
				}
			}
		} elseif(strstr($mins,'*/')) {
			$mult = str_replace('*/','',$mins);
			$startMin = $timedate->fromTimestmp($focus->date_time_start)->minute;
			$startFrom = ($startMin % $mult);
			
			for($i=$startFrom; $i<=59; $i+$mult) {
				if(($currentMin + $i) > 59) {
					$minName[] = ($i + $currentMin - 60);
				} else {
					$minName[] = ($i+$currentMin);
				}
				$i += $mult;
			}
		} elseif($mins != '*') {
			if(strstr($mins, ',')) {
				$exMins = explode(',',$mins);
				foreach($exMins as $k1 => $minGroup) {
					if(strstr($minGroup, '-')) {
						$exMinGroup = explode('-', $minGroup);
						for($i=$exMinGroup[0]; $i<=$exMinGroup[1]; $i++) {
							$minName[] = $i;
						}
					} else {
						$minName[] = $minGroup;
					}
				}
			} elseif(strstr($mins, '-')) {
				$exMinGroup = explode('-', $mins);
				for($i=$exMinGroup[0]; $i<=$exMinGroup[1]; $i++) {
					$minName[] = $i;
				}
			} else {
				$minName[] = $mins;
			}
		} 
		
		// prep some boundaries - these are not in GMT b/c gmt is a 24hour period, possibly bridging 2 local days
		if(empty($focus->time_from)  && empty($focus->time_to) ) {
			$timeFromTs = 0;
			$timeToTs = strtotime('+1 day');
		} else {
			$timeFromTs = strtotime($focus->time_from);	// these are now GMT (timestamps are all GMT)
			$timeToTs	= strtotime($focus->time_to);	// see above
			if($timeFromTs > $timeToTs) { // we've crossed into the next day 
				$timeToTs = strtotime('+1 day '. $focus->time_to);	// also in GMT
			}
		}
		$timeToTs++;
		
		if(empty($focus->last_run)) {
			$lastRunTs = 0;
		} else {
			$lastRunTs = strtotime($focus->last_run);
		}

		
		// now smush the arrays together =)
		$validJobTime = array();
		global $timedate;
		$dts = explode(' ',$focus->date_time_start); // split up datetime field into date & time
		
		$dts2 = $timedate->to_db_date_time($dts[0],$dts[1]); // get date/time into DB times (GMT)
		$dateTimeStart = $dts2[0]." ".$dts2[1];
		$timeStartTs = strtotime($dateTimeStart);
		if(!empty($focus->date_time_end) && !$focus->date_time_end == '2021-01-01 07:59:00') { // do the same for date_time_end if not empty
			$dte = explode(' ', $focus->date_time_end);
			$dte2 = $timedate->to_db_date_time($dte[0],$dte[1]);
			$dateTimeEnd = $dte2[0]." ".$dte2[1];
		} else {
			$dateTimeEnd = $timedate->getNow()->get('+1 day')->asDb();
//			$dateTimeEnd = '2020-12-31 23:59:59'; // if empty, set it to something ridiculous
		}
		$timeEndTs = strtotime($dateTimeEnd); // GMT end timestamp if necessary
		$timeEndTs++;
		/*_pp('hours:'); _pp($hrName);_pp('mins:'); _pp($minName);*/
		$nowTs = $timedate->getNow()->ts;

//		_pp('currentHour: '. $currentHour);
//		_pp('timeStartTs: '.date('r',$timeStartTs));
//		_pp('timeFromTs: '.date('r',$timeFromTs));
//		_pp('timeEndTs: '.date('r',$timeEndTs));
//		_pp('timeToTs: '.date('r',$timeToTs));
//		_pp('mktime: '.date('r',mktime()));
//		_pp('timeLastRun: '.date('r',$lastRunTs));
//		
//		_pp('hours: ');
//		_pp($hrName);
//		_pp('mins: ');
//		_ppd($minName);
		$hourSeen = 0;
		foreach($hrName as $kHr=>$hr) {
			$hourSeen++;
			foreach($minName as $kMin=>$min) {
				if($hr < $currentHour || $hourSeen == 25) {
					$theDate = $timedate->asDbDate($timedate->getNow()->get('+1 day'));
				} else {
					$theDate = $timedate->nowDbDate();		
				}
				$tsGmt = strtotime($theDate.' '.str_pad($hr,2,'0',STR_PAD_LEFT).":".str_pad($min,2,'0',STR_PAD_LEFT).":00"); // this is LOCAL
//				_pp(date('Y-m-d H:i:s',$tsGmt));
				
				if( $tsGmt >= $timeStartTs ) { // start is greater than the date specified by admin
					if( $tsGmt >= $timeFromTs ) { // start is greater than the time_to spec'd by admin
						if( $tsGmt <= $timeEndTs ) { // this is taken care of by the initial query - start is less than the date spec'd by admin
							if( $tsGmt <= $timeToTs ) { // start is less than the time_to
								if( $tsGmt >= $nowTs ) { // we only want to add jobs that are in the future
									if( $tsGmt > $lastRunTs ) { //TODO figure if this is better than the above check
										$validJobTime[] = $timedate->fromTimestamp($tsGmt)->asDb(); //_pp("Job Qualified for: ".date('Y-m-d H:i:s', $tsGmt));
									} else {
										//_pp('Job Time is NOT greater than Last Run');
									}
								} else {
									//_pp('Job Time is NOT larger than NOW'); _pp(date('Y-m-d H:i:s', $nowTs));
								}
							} else {
								//_pp('Job Time is NOT smaller that TimeTO: '.$tsGmt .'<='. $timeToTs);	
							}
						} else {
							//_pp('Job Time is NOT smaller that DateTimeEnd: '.date('Y-m-d H:i:s',$tsGmt) .'<='. $dateTimeEnd); _pp( $tsGmt .'<='. $timeEndTs );
						}
					} else {
						//_pp('Job Time is NOT bigger that TimeFrom: '.$tsGmt .'>='. $timeFromTs);
					}
				} else {
					//_pp('Job Time is NOT Bigger than DateTimeStart: '.date('Y-m-d H:i',$tsGmt) .'>='. $dateTimeStart);
				}
			}
		}
//		_ppd();
//		_ppd($validJobTime);
		return $validJobTime;
		
	}

	/**
	 * This function takes an array of jobs build up by retrieveSchedulers and
	 * puts them into the schedulers_times table
	 */
	function insertSchedules() {
		$GLOBALS['log']->info('----->Scheduler retrieving scheduled items and adding them to Job queue.');
		$jobsArr = $this->retrieveSchedulers();
		if(is_array($jobsArr['ids']) && !empty($jobsArr['ids']) && is_array($jobsArr['times']) && !empty($jobsArr['times'])) {
			foreach($jobsArr['ids'] as $k => $ids) {
				foreach($jobsArr['times'][$k] as $j => $time) {
					$guid = create_guid();
					$q = "INSERT INTO schedulers_times
						(id, deleted, date_entered, date_modified, scheduler_id, execute_time, status)
						VALUES (
						'".$guid."',
						0, 
						".db_convert("'".TimeDate::getInstance()->nowDb()."'", 'datetime').",
						".db_convert("'".TimeDate::getInstance()->nowDb()."'", 'datetime').",
						'".$jobsArr['ids'][$k]."',
						".db_convert("'".$time."'", 'datetime').",
						'ready'
						)";
						$this->db->query($q);
					$GLOBALS['log']->info('Query: '.$q);
				}
			}
		}
	}

	/**
	 * This function drops all rows in the schedulers_times table.
	 */
	function dropSchedules($truncate=false) {
		global $sugar_config;
		
		if(empty($this->db)) {
			$this->db = DBManagerFactory::getInstance();	
		}
		//BEGIN SUGARCRM flav=int ONLY
		$truncate = true;
		//END SUGARCRM flav=int ONLY
		
		if($truncate) {
            $query = $this->db->truncateTableSQL('schedulers_times');
			$this->db->query($query);
			$GLOBALS['log']->debug('----->Scheduler TRUNCATED ALL Jobs: '.$query);
		} else {
			$query = 'UPDATE schedulers_times SET deleted = 1';
			$this->db->query($query);
			$GLOBALS['log']->debug('----->Scheduler soft deleting all Jobs: '.$query);
		}
		//TODO make sure this will fail gracefully
	}
	
	/**
	 * This function retrieves valid jobs, parses the cron format, then returns
	 * an array of [JOB_ID][EXEC_TIME][JOB]
	 * 
	 * @return	$executeJobs	multi-dimensional array 
	 * 							[job_id][execute_time]
	 */
	function retrieveSchedulers() {
		$GLOBALS['log']->info('Gathering Schedulers');
		$executeJobs = array();
		$query 	= "SELECT id " .
				"FROM schedulers " .
				"WHERE deleted=0 " .
				"AND status = 'Active' " .
				"AND date_time_start < ".db_convert("'".TimeDate::getInstance()->nowDb()."'",'datetime')." " .
				"AND (date_time_end > ".db_convert("'".TimeDate::getInstance()->nowDb()."'",'datetime')." OR date_time_end IS NULL)";
				
		$result	= $this->db->query($query);
		$rows=0;
		$executeTimes = array();
		$executeIds = array();
		$executeJobTimes = array();
		while(($arr = $this->db->fetchByAssoc($result)) != null) {
			$focus = BeanFactory::getBean('Schedulers', $arr['id']);
			$executeTimes[$rows] = $this->deriveDBDateTimes($focus);
			if(count($executeTimes) > 0) {
				foreach($executeTimes as $k => $time) {
				$executeIds[$rows] = $focus->id;
					$executeJobTimes[$rows] = $time;
				}
			}
			$rows++;
		}
		$executeJobs['ids'] = $executeIds;
		$executeJobs['times'] = $executeJobTimes;
		return $executeJobs;
	}

} // end SchedulerDaemon class desc.

