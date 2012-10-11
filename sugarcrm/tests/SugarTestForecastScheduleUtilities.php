<?php
//FILE SUGARCRM flav=pro ONLY
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
 * SugarTestForecastScheduleUtilities.php
 *
 * This is a test class to create test ForecastSchedule instances
 */

require_once 'modules/ForecastSchedule/ForecastSchedule.php';
require_once 'modules/TimePeriods/TimePeriod.php';

class SugarTestForecastScheduleUtilities
{
    private static $_createdForecastSchedules = array();

    /**
     * @static
     * This is a static function to create a test ForecastSchedule instance
     * @param $timeperiod TimePeriod instance for ForecastSchedule
     * @param $user User assigned to ForecastSchedule
     * @return ForecastSchedule Mixed ForecastSchedule test instance
     */
    public static function createForecastSchedule($timeperiod, $user)
    {
        $forecastSchedule = new ForecastSchedule();
        $forecastSchedule->timeperiod_id = $timeperiod->id;
        $forecastSchedule->forecast_start_date = $timeperiod->start_date;
        $forecastSchedule->cascade_hierarchy = 0;
        $forecastSchedule->status = 'Active';
        $forecastSchedule->user_id = $user->id;
        $forecastSchedule->save();
        self::$_createdForecastSchedules[] = $forecastSchedule;
        return $forecastSchedule;
    }

    /**
     * @static
     * This is a static function to remove all created test ForecastSchedule instance
     *
     */
    public static function removeAllCreatedForecastSchedules()
    {
        $forecastSchedule_ids = self::getCreatedForecastScheduleIds();
        $GLOBALS['db']->query('DELETE FROM forecast_schedule WHERE id IN (\'' . implode("', '", $forecastSchedule_ids) . '\')');
    }

    /**
     * @static
     * This is a static function to return all ids of created ForecastSchedule instances
     *
     * @return array of ids of the ForecastSchedule instances created
     */
    public static function getCreatedForecastScheduleIds()
    {
        $forecastSchedule_ids = array();
        foreach (self::$_createdForecastSchedules as $fs)
        {
            $forecastSchedule_ids[] = $fs->id;
        }
        return $forecastSchedule_ids;
    }
}