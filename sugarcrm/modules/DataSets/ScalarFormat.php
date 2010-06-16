<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: ScalarFormat.php 45763 2009-04-01 19:16:18Z majed $
 * Description:
 ********************************************************************************/






Class ScalarFormat {
	
	var $interval_start;
	var $current_interval;
	var $interval;	
	
	function format_scalar($scalar, $scalar_type, $scalar_value){
		
		$split_query = preg_split('{{sc}}', $scalar_value);
		
		if(isset($split_query[1]) && is_numeric($split_query[1])){
			$this->interval_start = $split_query[1];
		} else {
			$this->interval_start = 0;

		}	
		if(isset($split_query[2]) && is_numeric($split_query[2])){
			$this->current_interval = $split_query[2];
		} else {
			$this->current_interval = 0;
		}
		
		$this->interval = $this->interval_start + $this->current_interval;
		
		if($scalar=="Year"){
			$display = $this->format_year($scalar_type);
		}
		
		if($scalar=="Quarter"){
			$display = $this->format_quarter($scalar_type);
		}
		
		if($scalar=="Month"){
			$display = $this->format_month($scalar_type);
		}
		
		if($scalar=="Week"){
			$display = $this->format_week($scalar_type);
		}
		
		if($scalar=="Day"){
			$display = $this->format_day($scalar_type);
		}
		
		return $display;
		
	//end function format scalar	
	}		
	
	
	function format_year($scalar_type){
		
		
		$scalar_unixstamp  = mktime(0, 0, 0, date("m"),  date("d"),  date("Y")+($this->interval));
		$scalar_display = date("Y", $scalar_unixstamp);
		
		return $scalar_display;

	//end function format_year;	
	}

	function format_quarter($scalar_type){
		
		
		$scalar_unixstamp  = mktime(0, 0, 0, date("m")+($this->interval*3),  date("d"),  date("Y"));
		
		//figure out what quarter this is in
		$month_number = date("n", $scalar_unixstamp);
		
		if($month_number<=3) $quarter_value = "Q1";
		if($month_number<=6 && $month_number>3) $quarter_value = "Q2";
		if($month_number<=9 && $month_number>6) $quarter_value = "Q3";
		if($month_number<=12 && $month_number>9) $quarter_value = "Q4";
		
		$scalar_display = date("Y", $scalar_unixstamp);
		$scalar_display = $quarter_value." ".$scalar_display;
		
		return $scalar_display;
			
	//end function format_year;	
	}	
	
	function format_month($scalar_type){
		
		
		$scalar_unixstamp  = mktime(0, 0, 0, date("m")+($this->interval),  date("d"),  date("Y"));
		$scalar_display = date("M Y", $scalar_unixstamp);
		
		
		//F would be a full representation of the Month
		//this is where the concept of Scalar Type comes into play.
		
		return $scalar_display;
			
	//end function format_year;	
	}	

	function format_week($scalar_type){
		
		$scalar_unixstamp  = mktime(0, 0, 0, date("m"),  date("d")+($this->interval*7),  date("Y"));
		
		$day_of_week = date("w", $scalar_unixstamp);

		$start_stamp = mktime(0, 0, 0, date("m", $scalar_unixstamp),  date("d", $scalar_unixstamp)-($day_of_week),  date("Y", $scalar_unixstamp));

		$scalar_display = "Week of: ".date("M jS, Y", $start_stamp);
		
		return $scalar_display;
			
	//end function format_year;	
	}

	function format_day($scalar_type){
		
		
		$scalar_unixstamp  = mktime(0, 0, 0, date("m"),  date("d")+($this->interval),  date("Y"));
		$scalar_display = date("D M jS, Y", $scalar_unixstamp);

		return $scalar_display;
			
	//end function format_year;	
	}



//end class ScalarFormat
}













?>