<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


/**
 * SugarTestTimePeriodUtilities.php
 *
 * This is a test class to create test TimePeriod instances
 */

require_once 'modules/TimePeriods/TimePeriod.php';

class SugarTestTimePeriodUtilities
{
    private static $_createdTimePeriods = array();

    private function __construct() {}

    /**
     * @static
     * This is a static function to create a test TimePeriod instance
     *
     * @param $start_date String value of a db date default start date
     * @param $end_date String value of a db date default end date
     * @return TimePeriod Mixed TimePeriod test instance
     */
    public static function createTimePeriod($start_date='', $end_date='')
    {
        global $timedate;
        $timedate = TimeDate::getInstance();
        $now = $timedate->getNow();
        $month = $timedate->getNow()->format('n');
        if($month < 4)
        {
            $month = 1;
        } else if ($month < 8) {
            $month = 4;
        } else if ($month < 11) {
            $month = 7;
        } else {
            $month = 10;
        }

        $year = $timedate->getNow()->format('Y');
        $time = mt_rand();
        $name = 'SugarTimePeriod' . $time;
        $timeperiod = new TimePeriod();

        if(empty($start_date))
        {
            $start_date = $timedate->asDbDate($now->get_day_begin(1, $month, $year));
        }

        if(empty($end_date))
        {
            $end_date =  $timedate->asDbDate($now->get_day_end(31, $month+2, $year));
        }

        $timeperiod->start_date = $start_date;
        $timeperiod->end_date = $end_date;
        $timeperiod->name = $name;
        $timeperiod->is_fiscal_year = 0;
        $timeperiod->save();
        self::$_createdTimePeriods[] = $timeperiod;
        return $timeperiod;
    }

    public static function createAnnualTimePeriod (){

        global $timedate;
        $timedate = TimeDate::getInstance();
        $start_date = $timedate->getNow();
        $month = $timedate->getNow()->format('n');
        if($month < 4)
        {
            $month = 1;
        } else if ($month < 8) {
            $month = 4;
        } else if ($month < 11) {
            $month = 7;
        } else {
            $month = 10;
        }


        $year = $timedate->getNow()->format('Y');
        $time = mt_rand();
        $name = 'SugarAnnualTimePeriod' . $time;
        $start_date->setDate($year, $month, 1);
        $timeperiod = new AnnualTimePeriod($timedate->asUserDate($start_date));

        $timeperiod->name = $name;
        $timeperiod->time_period_type = "Annual";
        $timeperiod->is_fiscal_year = 0;
        $timeperiod->is_leaf = 0;
        $timeperiod->save();
        self::$_createdTimePeriods[] = $timeperiod;
        return $timeperiod;
    }

    public static function addTimePeriod($timeperiod=NULL) {
        if(is_null($timeperiod)) {
            return;
        }
        self::$_createdTimePeriods[] = $timeperiod;
    }

    /**
     * @static
     * This is a static function to remove all created test TimePeriod instance
     *
     */
    public static function removeAllCreatedTimePeriods()
    {
        $timeperiod_ids = self::getCreatedTimePeriodIds();
        $GLOBALS['db']->query('DELETE FROM timeperiods WHERE id IN (\'' . implode("', '", $timeperiod_ids) . '\')');
    }

    /**
     * @static
     * This is a static function to return all ids of created TimePeriod instances
     *
     * @return array of ids of the TimePeriod instances created
     */
    public static function getCreatedTimePeriodIds()
    {
        $timeperiod_ids = array();
        foreach (self::$_createdTimePeriods as $tp)
        {
            $timeperiod_ids[] = $tp->id;
        }
        return $timeperiod_ids;
    }
}