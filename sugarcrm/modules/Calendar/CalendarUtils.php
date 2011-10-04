<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

/**
 * Created by JetBrains PhpStorm.
 * User: admin
 * Date: 9/29/11
 * Time: 11:55 AM
 * To change this template use File | Settings | File Templates.
 */

class CalendarUtils {

	/**
	 * Returns true if out of working days
	 * @param integer $i hours
	 * @param integer $j minutes
	 * @param integer $r_start start of working day in minutes
	 * @param integer $r_end end of working day in minutes
	 * @return boolean
	 */
	static function check_owt($i,$j,$r_start,$r_end){
		if($i*60+$j < $r_start || $i*60+$j >= $r_end)
			return true;
	}	
	
	/**
	 * Convert timestamp to date string using defined format or user's format by default
	 * @param integer $t timestamp
	 * @param string $format date format
	 * @return string
	 */
	static function timestamp_to_string($t,$format = false){
		global $timedate;
		if($format == false)
			$f = $timedate->get_date_time_format();
		else
			$f = $format;
		return date($f,$t - date('Z',$t) );
	}
	
	/**
	 * Convert user formated date to timestamp
	 * @param string $d date 
	 * @return integer timestamp
	 */
	static function to_timestamp_from_uf($d){
		$db_d = $GLOBALS['timedate']->swap_formats($d,$GLOBALS['timedate']->get_date_time_format(),'Y-m-d H:i:s');
		$ts_d = CalendarUtils::to_timestamp($db_d);
		return $ts_d;
	}	
	
	/**
	 * Convert Y-m-d to timestamp without any timezone offset
	 * @param string $db_date 
	 * @return integer timestamp
	 */
	static function to_timestamp($db_date){
		$date_parsed = date_parse($db_date);
		$t = gmmktime($date_parsed['hour'],$date_parsed['minute'],$date_parsed['second'],$date_parsed['month'],$date_parsed['day'],$date_parsed['year']);
		return $t;
	}
	
	
	/**
	 * Returns list of needed fields for modules
	 * @return array
	 */
	static function get_fields(){
		return array(
			'Meetings' => array(
				'name',
				'date_start',
				'duration_hours',
				'duration_minutes',
				'status',
				'description',
				'parent_type',
				'parent_name',
				'parent_id',
			),
			'Calls' => array(
				'name',
				'date_start',
				'duration_hours',
				'duration_minutes',
				'status',
				'description',
				'parent_type',
				'parent_name',
				'parent_id',
			),
			'Tasks' => array(
				'name',
				'date_start',
				'date_due',
				'status',
				'description',
				'parent_type',
				'parent_name',
				'parent_id',
			),
		);
	}
}
