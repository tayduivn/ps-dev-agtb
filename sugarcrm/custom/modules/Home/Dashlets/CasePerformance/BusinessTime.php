<?php
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
 /*********************************************************************************
 * $Id: BusinessTime.php,v 1.0 2006/07/30 21:08:15 matt Exp $
 * Description: Business Time Class
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/


/*
 * Example Useage:
 *
 *
	$opentime = array(
	     "Mon" => array(
	        "open" => "09:00:00",
	        "close" => "17:00:00",
	        ),
	    "Tue" => array(
	        "open" => "09:00:00",
	        "close" => "17:00:00",
	        ),
	    "Wed" => array(
	        "open" => "09:00:00",
	        "close" => "17:00:00",
	        ),
	    "Thu" => array(
	        "open" => "09:00:00",
	        "close" => "17:00:00",
	        ),
	    "Fri" => array(
	        "open" => "09:00:00",
	        "close" => "17:00:00",
	        ),
	    );

	$start = strtotime("2005-01-18 12:00:00");
	$end = strtotime("2007-01-18 12:00:00");

	$bt=new BusinessTime();

	$bt->start = $start;
	$bt->end = $end;
	$bt->opentime = $opentime;

	$time = $bt->calculate();

	var_export($time);

 *
 *
 *
 */


class BusinessTimeForCP {

	var $opentime;
	var $start;
	var $end;
	var $holiday = array();

	function BusinessTimeForCP(){
		date_default_timezone_set("UTC");
	}

    /**
     * Calculate the time that a business spends open and closed based on a task
     *
     * @return array Contains the keys "ontime" & "offtime" with the values in seconds (int)
     */
    function calculate(){

		$onTime=array();
		$offTime = array();
		$result= array();
		$n=0;

		if ($this->end <= $this->start) {
			$result['offtime'] = 0;
			$result['ontime'] = 0;
			return $result;
			exit;
		}

		//start with triming start time if the start time is before the beginning of the work day. Then count offtime.
		$currentTime = $this->nextOpenOffice($this->start);
		if ($currentTime != $this->start) {
			$offTimeStart = $currentTime - $this->start;
		}

		while($currentTime < $this->end){

			$cob = $this->nextCloseOffice($currentTime);
			$nextSob = $this->nextOpenOffice($cob);

			//check to see if the end of the day is before the close of business that day.
			if ($this->end <= $cob){
				$onTime[$n]= $this->end - $currentTime;
			//check to see if the end of task is after the close of business.  If so add the whole day and the calc the offtime as well
			} elseif($this->end > $cob && $this->end < $nextSob) {
				$onTime[$n] = $cob - $currentTime;
				$offTime[$n] = $this->end- $cob;
			//check to see if the end of the task is after the close of business
			} elseif($this->end > $cob && $this->end > $nextSob) {
				$onTime[$n] = $cob - $currentTime;
				$offTime[$n] = $nextSob - $cob;
			}

			//add the time on & off time to the start time of the task to calculate the current time of the loop
			$timeElapsed = $onTime[$n];
			if (isset($offTime[$n])) $timeElapsed = $timeElapsed + $offTime[$n];
			$currentTime = $currentTime + $timeElapsed;
			$n++;
		}

		$result['ontime']= array_sum($onTime);
		if (isset($offTimeStart)){
			$result['offtime']= array_sum($offTime)+$offTimeStart;
		} else {
			$result['offtime']= array_sum($offTime);
		}
		return $result;
    }

    /**
     * Determine if the business is open
     *
     * @param string $time The start time (since UNIX epoch) of the given event
     *
     * @return bool true or false
     */
    function openOffice($time){
		$time_str = date("Y-m-d H:i:s", $time);
        $datetime = explode (" ", $time_str);

        $this->opentime = array_merge($this->opentime, $this->holiday);

        //check to see if the time is in the workday
        $dotw = date("D", $time);


	   if (array_key_exists($dotw, $this->opentime) && !array_key_exists($datetime[0], $this->opentime)) {

            $sod = strtotime($datetime[0] . " " . $this->opentime[$dotw]['open']);
            $eod = strtotime($datetime[0] . " " . $this->opentime[$dotw]['close']);

            if ($eod <= $sod){
            	$eod = $eod + ((24*60)*60);
            }

            if ($time >= $sod && $time < $eod) {
                return true;
            }
        }
        return false;
    }

    /**
     * Calculate the next start of day
     *
     * @param int $time The start time (since Unix Epoch) of the given event
     *
     * @return int The next start of day returned in seconds since Unix Epoch
     */
    function nextOpenOffice($time){

    	if ($this->openOffice($time)){
    		return $time;
    	}

		$time_str = date("Y-m-d H:i:s", $time);
		$this->opentime = array_merge($this->opentime, $this->holiday);
	    $datetime = explode (" ", $time_str);
	    $dotw = date("D", $time);
		$n = 0; // number of days we must advance the date
		$t  = explode(":", $datetime[1]);

		if (array_key_exists($dotw, $this->opentime)) {
		// check to see if the it is after we open, if so, advance on day.
			if ($this->opentime[$dotw]['open'] < $datetime[1] || array_key_exists($datetime[0], $this->opentime)) {
				switch ($dotw){
					case "Mon":
						$dotw = "Tue";
						break;
					case "Tue":
						$dotw = "Wed";
						break;
					case "Wed":
						$dotw = "Thu";
						break;
					case "Thu":
						$dotw = "Fri";
						break;
					case "Fri":
						$dotw = "Sat";
						break;
					case "Sat":
						$dotw = "Sun";
						break;
					case "Sun":
						$dotw = "Mon";
						break;
				}

				//add 1 to the date since we switched days
				$n = 1;
				$datetime[0] =  date("Y-m-d" , mktime($t[0], $t[1], $t[2], date("m", $time)  , date("d", $time)+$n, date("Y", $time)));
			}
		}
		//evaluate if the business is open on that day, if not,find the next available open day.
		while(!array_key_exists($dotw, $this->opentime) || array_key_exists($datetime[0], $this->opentime)){

			switch ($dotw){
				case "Mon":
					$dotw = "Tue";
					break;
				case "Tue":
					$dotw = "Wed";
					break;
				case "Wed":
					$dotw = "Thu";
					break;
				case "Thu":
					$dotw = "Fri";
					break;
				case "Fri":
					$dotw = "Sat";
					break;
				case "Sat":
					$dotw = "Sun";
					break;
				case "Sun":
					$dotw = "Mon";
					break;
			}

			//advance the date 1 day for each loop
			$n++;
			$datetime[0] =  date("Y-m-d" , mktime($t[0], $t[1], $t[2], date("m", $time)  , date("d", $time)+$n, date("Y", $time)));
		}

		if ($n > 0  || ($this->opentime[$dotw]['open'] > $datetime[1] && $n == 0 )) {
			$start = $this->opentime[$dotw]['open'];
		} else {
			$start = $datetime[1];
		}

		return  strtotime($datetime[0] . " " . $start);
    }

    /**
     * Calculate the next end of day
     *
     * @param int $time The start time (since UNIX Epoch) of the given event
     *
     * @return int The next end of day returned in seconds since UNIX epoch
     */
    function nextCloseOffice ($time){

		$time_str = date("Y-m-d H:i:s", $time);

		$datetime = explode (" ", $time_str);
    	$dotw = date("D", $time);
    	$this->opentime = array_merge($this->opentime, $this->holiday);
    	$t  = explode(":", $datetime[1]);
    	$n=0;

		$start =  strtotime($datetime[0] . " " . $this->opentime[$dotw]['open']);
		$cob =  strtotime($datetime[0] . " " . $this->opentime[$dotw]['close']);

		if ($start > $cob) {
			$cob = $cob +((24*60)*60);
		}

		if(array_key_exists($dotw, $this->opentime) && !array_key_exists($datetime[0], $this->opentime) && $cob > $time){
			return $cob;
		}
        while(!array_key_exists($dotw, $this->opentime) || array_key_exists($datetime[0],$this->opentime)|| $cob < $time){

            switch ($dotw){
                case "Mon":
	                $dotw = "Tue";
	                break;
	            case "Tue":
	                $dotw = "Wed";
	                break;
	            case "Wed":
	                $dotw = "Thu";
	                break;
	            case "Thu":
	                $dotw = "Fri";
	                break;
	            case "Fri":
	                $dotw = "Sat";
	                break;
	            case "Sat":
	                $dotw = "Sun";
	                break;
	            case "Sun":
	                $dotw = "Mon";
	                break;
            }

            //advance the date 1 day for each loop
            $n++;
            //split the datetime up to make a new UNIX timestamp for the next available workday

            $datetime[0] =  date("Y-m-d" , mktime($t[0], $t[1], $t[2], date("m", $time)  , date("d", $time)+$n, date("Y", $time)));
        }

        if ($n > 0  || ($this->opentime[$dotw]['close'] < $datetime[1] && $n == 0 )) {
            $end = $this->opentime[$dotw]['close'];
        } else {
            $end = $datetime[1];
        }

        $return = strtotime($datetime[0] . " " . $end);

		return  $return;
    }

}
?>
