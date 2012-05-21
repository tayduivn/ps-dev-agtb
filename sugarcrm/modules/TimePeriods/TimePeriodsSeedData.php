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

/**
 * TimePeriodsSeedData.php
 *
 * This is a class used for creating TimePeriodsSeedData.  We moved this code out from install/populateSeedData.php so
 * that we may better control and test creating default timeperiods.
 *
 */

class TimePeriodsSeedData {

/**
 * populateSeedData
 *
 * This is a static function to create TimePeriods.
 *
 * @static
 * @return array Array of TimePeriods created
 */
public static function populateSeedData()
{
    $timedate = TimeDate::getInstance();
    $now = $timedate->getNow();
    $timedate->tzUser($now); // use local TZ to calculate dates
    $timeperiods=array();

    $timeperiod = new TimePeriod();
    $year = $timedate->getNow()->format('Y');
    $timeperiod->name = "Year ".$year;
    $timeperiod->start_date = $timedate->asDbDate($now->get_day_begin(1, 1, $year));
    $timeperiod->end_date = $timedate->asDbDate($now->get_day_end(31, 12, $year));
    $timeperiod->is_fiscal_year =1;
    $fiscal_year_id=$timeperiod->save();

    //create a time period record for the first quarter.
    $timeperiod = new TimePeriod();
    $timeperiod->name = "Q1 ".$year;
    $timeperiod->start_date = $timedate->asDbDate($now->get_day_begin(1, 1, $year));
    $timeperiod->end_date =  $timedate->asDbDate($now->get_day_end(31, 3, $year));
    $timeperiod->is_fiscal_year =0;
    $timeperiod->parent_id=$fiscal_year_id;
    $current_timeperiod_id = $timeperiod->save();
    $timeperiods[$current_timeperiod_id]=$timeperiod->start_date;
    //create a timeperiod record for the 2nd quarter.
    $timeperiod = new TimePeriod();
    $timeperiod->name = "Q2 ".$year;
    $timeperiod->start_date = $timedate->asDbDate($now->get_day_begin(1, 4, $year));
    $timeperiod->end_date =  $timedate->asDbDate($now->get_day_end(30, 6, $year));
    $timeperiod->is_fiscal_year =0;
    $timeperiod->parent_id=$fiscal_year_id;
    $current_timeperiod_id = $timeperiod->save();
    $timeperiods[$current_timeperiod_id]=$timeperiod->start_date;
    //create a timeperiod record for the 3rd quarter.
    $timeperiod = new TimePeriod();
    $timeperiod->name = "Q3 ".$year;
    $timeperiod->start_date = $timedate->asDbDate($now->get_day_begin(1, 7, $year));
    $timeperiod->end_date =  $timedate->asDbDate($now->get_day_end(31, 10, $year));
    $timeperiod->is_fiscal_year =0;
    $timeperiod->parent_id=$fiscal_year_id;
    $current_timeperiod_id = $timeperiod->save();
    $timeperiods[$current_timeperiod_id]=$timeperiod->start_date;
    //create a timeperiod record for the 4th quarter.
    $timeperiod = new TimePeriod();
    $timeperiod->name = "Q4 ".$year;
    $timeperiod->start_date = $timedate->asDbDate($now->get_day_begin(1, 10, $year));
    $timeperiod->end_date =  $timedate->asDbDate($now->get_day_end(31, 12, $year));
    $timeperiod->is_fiscal_year =0;
    $timeperiod->parent_id=$fiscal_year_id;
    $current_timeperiod_id = $timeperiod->save();
    $timeperiods[$current_timeperiod_id]=$timeperiod->start_date;

    //Create another set of timeperiod records for the following year
    $year = $timedate->getNow()->modify('+1 year')->format('Y');
    $timeperiod = new TimePeriod();
    $timeperiod->name = "Year ".$year;
    $timeperiod->start_date = $timedate->asDbDate($now->get_day_begin(1, 1, $year));
    $timeperiod->end_date = $timedate->asDbDate($now->get_day_end(31, 12, $year));
    $timeperiod->is_fiscal_year =1;
    $fiscal_year_id=$timeperiod->save();

    //create a time period record for the first quarter next year.
    $timeperiod = new TimePeriod();
    $timeperiod->name = "Q1 ".$year;
    $timeperiod->start_date = $timedate->asDbDate($now->get_day_begin(1, 1, $year));
    $timeperiod->end_date =  $timedate->asDbDate($now->get_day_end(31, 3, $year));
    $timeperiod->is_fiscal_year =0;
    $timeperiod->parent_id=$fiscal_year_id;
    $current_timeperiod_id = $timeperiod->save();
    $timeperiods[$current_timeperiod_id]=$timeperiod->start_date;
    //create a timeperiod record for the 2nd quarter next year.
    $timeperiod = new TimePeriod();
    $timeperiod->name = "Q2 ".$year;
    $timeperiod->start_date = $timedate->asDbDate($now->get_day_begin(1, 4, $year));
    $timeperiod->end_date =  $timedate->asDbDate($now->get_day_end(30, 6, $year));
    $timeperiod->is_fiscal_year =0;
    $timeperiod->parent_id=$fiscal_year_id;
    $current_timeperiod_id = $timeperiod->save();
    $timeperiods[$current_timeperiod_id]=$timeperiod->start_date;
    //create a timeperiod record for the 3rd quarter next year.
    $timeperiod = new TimePeriod();
    $timeperiod->name = "Q3 ".$year;
    $timeperiod->start_date = $timedate->asDbDate($now->get_day_begin(1, 7, $year));
    $timeperiod->end_date =  $timedate->asDbDate($now->get_day_end(31, 10, $year));
    $timeperiod->is_fiscal_year =0;
    $timeperiod->parent_id=$fiscal_year_id;
    $current_timeperiod_id = $timeperiod->save();
    $timeperiods[$current_timeperiod_id]=$timeperiod->start_date;
    //create a timeperiod record for the 4th quarter next year.
    $timeperiod = new TimePeriod();
    $timeperiod->name = "Q4 ".$year;
    $timeperiod->start_date = $timedate->asDbDate($now->get_day_begin(1, 10, $year));
    $timeperiod->end_date =  $timedate->asDbDate($now->get_day_end(31, 12, $year));
    $timeperiod->is_fiscal_year =0;
    $timeperiod->parent_id=$fiscal_year_id;
    $current_timeperiod_id = $timeperiod->save();
    $timeperiods[$current_timeperiod_id]=$timeperiod->start_date;
    return $timeperiods;
}

}