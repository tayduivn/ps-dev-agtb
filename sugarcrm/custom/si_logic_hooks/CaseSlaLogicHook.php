<?php

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');


/**
 * Custom Case Logic Hook to calculate SLA time per IT Req #8364.  Not included in other
 * case logic hook files for maintainability.
 *
 * @author Andreas Sandberg
 * 
 */
class CaseSlaLogicHook
{

	//Note times offsets are in UTC format for caluclations.  13:00:00 equivlaent to 06 AM - 01:00:00 equivalent to 6 PM.
	var $opentime= array(
	     "Mon" => array(
	        "open" => "13:00:00",
	        "close" => "01:00:00",
	        ),
	    "Tue" => array(
	        "open" => "13:00:00",
	        "close" => "01:00:00",
	        ),
	    "Wed" => array(
	        "open" => "13:00:00",
	        "close" => "01:00:00",
	        ),
	    "Thu" => array(
	        "open" => "13:00:00",
	        "close" => "01:00:00",
	        ),
	    "Fri" => array(
	        "open" => "13:00:00",
	        "close" => "01:00:00",
	        ),
	    );
	
	    
	/**
	 * Custom logic hook for Cases to determine whether or not a case has met the required 
	 * SLA time for first response.  IT Req# 8364 
	 * @param SugarBean $focus
	 * @author Andreas Sandberg
	 */
	function slaMetCaseHook(&$focus, $event, $arguments)
	{
		if( isset($focus->initrespfailed_c) && ($focus->initrespfailed_c == 1) )
		{
			$GLOBALS['log']->debug("slaMetCaseHook:Case {$focus->id} has already been marked as violating sla agreement. Doing nothing.");
			return;
		}
		
		if( ! $this->_isNewRecord($focus) )
		{
			
			$calculate_sla = (( $focus->fetched_row['status'] == "New") && ($focus->status != "New")) ? TRUE : FALSE;
			if($calculate_sla)
			{
				//Date entered is not populated in the focus during save calls so we use the fetched row array to retrieve it.
				$date_entered = $focus->fetched_row['date_entered'];
				$GLOBALS['log']->debug("slaMetCaseHook:Initiating SLA logic hook with date_entered: {$date_entered}");
				//Use the existing Businesstime class written by Matt H. to perform the calculation in seconds
				//from now compared to when the case was entered.  Note that this class takes into account
				//that users can define the open hours for a business.  Since the following code is executed from
				//a logic hook we need to configure the open times statically which is from 6am-6pm.
				date_default_timezone_set("UTC");
			
				$start = strtotime($date_entered);
				
				$now = strtotime(date("Y-m-d H:i:s"));
				
				$sec_missed = $this->_calculateSLATime($start,$now);
				
				//If we couldn't calculate an sla time then FALSE is returned and an error is logged.
				if($sec_missed === FALSE)
					return; 
				
				$sec_in_minute = 60;
				$minutes_missed = ceil($sec_missed / $sec_in_minute);
				
				$over_sla_time_results = $this->_isCaseOverSlaTime($focus,$minutes_missed, $focus->priority_level,$focus->support_service_level_c);
					
				if($over_sla_time_results['is_over_sla_time'])
				{
					$GLOBALS['log']->debug("slaMetCaseHook:Initial Response time was over sla, adjusting case.");
					$focus->initrespfailed_c = 1;
					$focus->initrespminutesfail_c = $over_sla_time_results['over_sla_time'];
				}
				else 
				{
					$GLOBALS['log']->debug("slaMetCaseHook:Initial Response time was not over sla.");
					//Provide in minutes how much time was left over before sla deadline.
					$focus->initrespminutes_c = $over_sla_time_results['over_sla_time'];
				}
			}
			else 
				$GLOBALS['log']->debug("slaMetCaseHook: Case status has not been changed from new to *, no sla calculation needed.");
		}
	}
	
	/**
	 * Determine if a case had exceed the SLA time given their priority and sla.
	 *
	 * @param Bean $focus
	 * @param int $elapsed_minutes
	 * @param String $priority
	 * @param String $sla
	 * @return bool TRUE indicating the SLA was exceeded, FALSE otherwise
	 */
	function _isCaseOverSlaTime(&$focus, $elapsed_minutes, $priority = 'P3', $sla = 'standard')
	{
		$results = array('is_over_sla_time' => FALSE, 'over_sla_time' => 0);
		//Get the SLA definitions from the SLA Dashlet.
		require_once('custom/modules/Home/Dashlets/SLAcountdown/SLAcountdown.data.php');
		global $dashletData;
		
		$sla_definition = $dashletData['SLAcountdown']['sla'];
		
		$sla_max_time_in_minutes = isset($dashletData['SLAcountdown']['sla'][$priority][$sla]) ? $dashletData['SLAcountdown']['sla'][$priority][$sla] : FALSE;
		if($sla_max_time_in_minutes === FALSE)
		{
			$GLOBALS['log']->debug("slaMetCaseHook:Unable to find a valid SLA level for Case ID: {$focus->id}");
			return $results;
		}
	
		if($elapsed_minutes > $sla_max_time_in_minutes)
		{
			$GLOBALS['log']->debug("slaMetCaseHook: Case ID: {$focus->id} has exceeded SLA time.  SLA limit: {$sla_max_time_in_minutes}, Total Minutes Case Was Open: {$elapsed_minutes}");
			$results['is_over_sla_time'] = TRUE;
			$results['over_sla_time'] = $elapsed_minutes - $sla_max_time_in_minutes;
		}
		else 
		{
			//If the sla has been met, let's capture the number of minutes before it would have expired so we can report against that number for metrics.
			//is_over_sla_time defaults to false.
			$GLOBALS['log']->debug("slaMetCaseHook: Case ID: {$focus->id} has NOT exceeded SLA time.  SLA limit: {$sla_max_time_in_minutes}, Total Minutes Case Was Open: {$elapsed_minutes}");
			$results['over_sla_time'] = $sla_max_time_in_minutes - $elapsed_minutes;
		}
		return $results;
	}
	
	
	/**
	 * Function used to calculate time difference in seconds between two dates.  Holidays and working hours are taken into account.  This
	 * function utalizes the BusinessTimeForSLA class written by Matt Heitzenroder (Roder).
	 *
	 * @param int $start_time Represented in sec.
	 * @param int $end_time Represented in sec. (now)
	 * @return int	Total amount of seconds between the two dates not including holidays or off hours.  FALSE returned if error found.
	 */
	function _calculateSLATime($start_time, $end_time)
	{
		$GLOBALS['log']->debug("slaMetCaseHook:Calculating SLA Time with Start timestamp:$start_time and End timestamp:$end_time");
		
		require_once('custom/modules/Home/Dashlets/SLAcountdown/BusinessTime.php');

		if(empty($start_time))
		{
			$GLOBALS['log']->debug("slaMetCaseHook:Could not calculate sla response time in case logic hook: No date entered.");
			return FALSE;
		}

		$bt=new BusinessTimeForSLA();
		$bt->start = $start_time;
		$bt->end = $end_time;
		$bt->opentime = $this->opentime;
		
		//Get the holidays which are currently stored in the SLAcountdown dashlet so they can be maintained in a single location.
		require_once('custom/modules/Home/Dashlets/SLAcountdown/SLAcountdown.data.php');
		global $dashletData;
		$bt->holiday = $dashletData['SLAcountdown']['holidays'];
		
		//Perform the calculations.
		$time_results = $bt->calculate();

		$sec_time_results = isset($time_results['ontime']) ? $time_results['ontime'] : -1;
		if($sec_time_results <= -1)
		{
			$GLOBALS['log']->debug("slaMetCaseHook: Could not calculate sla response time in case logic hook: Invalid Time Results.");
			return FALSE;
		}

		return $sec_time_results;
	}
	
	/**
	* Determine if the focus is a new record or is an update to an already existing record. New records will have the fetched_row set to false
	* and will also have the new_with_id flag set to false.
	*
	* @param unknown_type $focus
	* @author Andreas Sandberg
	*/
	function _isNewRecord($focus)
	{
		$is_new = FALSE;
		if( $focus->fetched_row === FALSE && !$focus->new_with_id)
			$is_new = TRUE;
			
		return $is_new;
	}
}

