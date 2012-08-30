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

    $timeperiod1 = new TimePeriod();
    $year = $timedate->getNow()->format('Y');
    $timeperiod1->name = "Year ".$year;
    $timeperiod1->start_date = $timedate->asDbDate($now->get_day_begin(1, 1, $year));
    $timeperiod1->end_date = $timedate->asDbDate($now->get_day_end(31, 12, $year));
    $timeperiod1->is_fiscal_year =0;
    $timeperiod1->is_leaf = 0;
    $timeperiod1->time_period_type = "Annually";
    $fiscal_year_id=$timeperiod1->save();
    $current_timeperiod_id = $timeperiod1->save();
    $timeperiods[$current_timeperiod_id]=$timeperiod1;
/*
    //create a time period record for the first quarter.
    $timeperiod2 = new TimePeriod();
    $timeperiod2->name = "Q1 ".$year;
    $timeperiod2->start_date = $timedate->asDbDate($now->get_day_begin(1, 1, $year));
    $timeperiod2->end_date =  $timedate->asDbDate($now->get_day_end(31, 3, $year));
    $timeperiod2->is_fiscal_year =0;
    $timeperiod2->parent_id=$fiscal_year_id;
    $current_timeperiod_id = $timeperiod2->save();
    $timeperiods[$current_timeperiod_id]=$timeperiod2;
    //create a timeperiod record for the 2nd quarter.
    $timeperiod3 = new TimePeriod();
    $timeperiod3->name = "Q2 ".$year;
    $timeperiod3->start_date = $timedate->asDbDate($now->get_day_begin(1, 4, $year));
    $timeperiod3->end_date =  $timedate->asDbDate($now->get_day_end(30, 6, $year));
    $timeperiod3->is_fiscal_year =0;
    $timeperiod3->parent_id=$fiscal_year_id;
    $current_timeperiod_id = $timeperiod3->save();
    $timeperiods[$current_timeperiod_id]=$timeperiod3;
    //create a timeperiod record for the 3rd quarter.
    $timeperiod4 = new TimePeriod();
    $timeperiod4->name = "Q3 ".$year;
    $timeperiod4->start_date = $timedate->asDbDate($now->get_day_begin(1, 7, $year));
    $timeperiod4->end_date =  $timedate->asDbDate($now->get_day_end(31, 9, $year));
    $timeperiod4->is_fiscal_year =0;
    $timeperiod4->parent_id=$fiscal_year_id;
    $current_timeperiod_id = $timeperiod4->save();
    $timeperiods[$current_timeperiod_id]=$timeperiod4;
    //create a timeperiod record for the 4th quarter.
    $timeperiod5 = new TimePeriod();
    $timeperiod5->name = "Q4 ".$year;
    $timeperiod5->start_date = $timedate->asDbDate($now->get_day_begin(1, 10, $year));
    $timeperiod5->end_date =  $timedate->asDbDate($now->get_day_end(31, 12, $year));
    $timeperiod5->is_fiscal_year =0;
    $timeperiod5->parent_id=$fiscal_year_id;
    $current_timeperiod_id = $timeperiod5->save();
    $timeperiods[$current_timeperiod_id]=$timeperiod5;

    //Create another set of timeperiod records for the following year
    $year = $timedate->getNow()->modify('+1 year')->format('Y');
    $timeperiod6 = new TimePeriod();
    $timeperiod6->name = "Year ".$year;
    $timeperiod6->start_date = $timedate->asDbDate($now->get_day_begin(1, 1, $year));
    $timeperiod6->end_date = $timedate->asDbDate($now->get_day_end(31, 12, $year));
    $timeperiod6->is_fiscal_year =1;
    $fiscal_year_id=$timeperiod6->save();

    //create a time period record for the first quarter next year.
    $timeperiod7 = new TimePeriod();
    $timeperiod7->name = "Q1 ".$year;
    $timeperiod7->start_date = $timedate->asDbDate($now->get_day_begin(1, 1, $year));
    $timeperiod7->end_date =  $timedate->asDbDate($now->get_day_end(31, 3, $year));
    $timeperiod7->is_fiscal_year =0;
    $timeperiod7->parent_id=$fiscal_year_id;
    $current_timeperiod_id = $timeperiod7->save();
    $timeperiods[$current_timeperiod_id]=$timeperiod7->start_date;
    //create a timeperiod record for the 2nd quarter next year.
    $timeperiod8 = new TimePeriod();
    $timeperiod8->name = "Q2 ".$year;
    $timeperiod8->start_date = $timedate->asDbDate($now->get_day_begin(1, 4, $year));
    $timeperiod8->end_date =  $timedate->asDbDate($now->get_day_end(30, 6, $year));
    $timeperiod8->is_fiscal_year =0;
    $timeperiod8->parent_id=$fiscal_year_id;
    $current_timeperiod_id = $timeperiod8->save();
    $timeperiods[$current_timeperiod_id]=$timeperiod8;
    //create a timeperiod record for the 3rd quarter next year.
    $timeperiod9 = new TimePeriod();
    $timeperiod9->name = "Q3 ".$year;
    $timeperiod9->start_date = $timedate->asDbDate($now->get_day_begin(1, 7, $year));
    $timeperiod9->end_date =  $timedate->asDbDate($now->get_day_end(31, 9, $year));
    $timeperiod9->is_fiscal_year =0;
    $timeperiod9->parent_id=$fiscal_year_id;
    $current_timeperiod_id = $timeperiod9->save();
    $timeperiods[$current_timeperiod_id]=$timeperiod9;
    //create a timeperiod record for the 4th quarter next year.
    $timeperiod10 = new TimePeriod();
    $timeperiod10->name = "Q4 ".$year;
    $timeperiod10->start_date = $timedate->asDbDate($now->get_day_begin(1, 10, $year));
    $timeperiod10->end_date =  $timedate->asDbDate($now->get_day_end(31, 12, $year));
    $timeperiod10->is_fiscal_year =0;
    $timeperiod10->parent_id=$fiscal_year_id;
    $current_timeperiod_id = $timeperiod10->save();
    $timeperiods[$current_timeperiod_id]=$timeperiod10;
*/
    return $timeperiods;
}

}