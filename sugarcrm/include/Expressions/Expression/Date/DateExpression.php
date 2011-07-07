<?php
/************************************
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
require_once('include/Expressions/Expression/AbstractExpression.php');
require_once('include/TimeDate.php');
abstract class DateExpression extends AbstractExpression
{
	protected $internalDateFormat = "Y-m-d";
	protected $internalDateTimeFormat = "Y-m-d H:i:s";
	protected $includeTime = false;
	
	/**
	 * All parameters have to be a string.
	 */
	function getParameterTypes() {
		return AbstractExpression::$DATE_TYPE;
	}
	
	protected function convertToGMT($unixTime) {
		$TD = new TimeDate();
		$time = date($TD->get_date_time_format(), $unixTime);
		$time = $TD->to_db($time);
		return strtotime($time);
	}
	
	protected function convertToUserZone($unixTime) {
		$TD = new TimeDate();
		$time = date($this->internalDateFormat, $unixTime);
		return $TD->to_display_date($time);
	}
	
	/**
	 * returns the users display date format. 
	 *
	 * @param int $unixTime (should be in GMT Time zone)
	 */
	protected function toDisplayTime($unixTime) {
		$TD = new TimeDate();
		if ($this->includeTime)
		{
			return  date($TD->get_date_format() . " " . $TD->get_time_format(), $unixTime);
		}
		return date($TD->get_date_format(), $unixTime);
	}
	
	protected function convertFromUserFormat($date) {
		$TD = new TimeDate();
		if (strrchr(trim($date), ' ')) {
			$this->includeTime = true;
			$date = $TD->swap_formats($date, $TD->get_date_time_format(), $this->internalDateTimeFormat);
		}
		 else {
		 	$date = $TD->swap_formats($date, $TD->get_date_format(), $this->internalDateFormat);
		 }
		return $date;
	}
}
?>