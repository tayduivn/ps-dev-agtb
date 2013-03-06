<?php
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

require_once('include/SugarForecasting/AbstractForecast.php');
class SugarForecasting_Filter_TimePeriodFilter extends SugarForecasting_AbstractForecast
{

    /**
     * Process to get an array of Timeperiods based on system configurations.  It will return the n number
     * of backward timeperiods + current set of timeperiod + n number of future timeperiods.
     *
     * @return array id/name of TimePeriods
     */
    public function process()
    {
        $admin = BeanFactory::getBean('Administration');
        $settings = $admin->getConfigForModule('Forecasts', 'base');
        $forward = $settings['timeperiod_shown_forward'];
        $backward = $settings['timeperiod_shown_backward'];
        $type = $settings['timeperiod_interval'];
        $leafType = $settings['timeperiod_leaf_interval'];
        $timedate = TimeDate::getInstance();

        $timePeriods = array();

        $current = TimePeriod::getCurrentTimePeriod($type);

        //If the current TimePeriod cannot be found for the type, just create one using the current date as a reference point
        if(empty($current)) {
            $current = TimePeriod::getByType($type);
            $current->setStartDate($timedate->getNow()->asDbDate());
        }

        $startDate = $timedate->fromDbDate($current->start_date);

        //Move back for the number of backward TimePeriod(s)
        while($backward-- > 0) {
            $startDate->modify($current->previous_date_modifier);
        }

        $endDate = $timedate->fromDbDate($current->end_date);

        //Increment for the number of forward TimePeriod(s)
        while($forward-- > 0) {
            $endDate->modify($current->next_date_modifier);
        }

        $db = DBManagerFactory::getInstance();
        $query = sprintf("SELECT id, name FROM timeperiods WHERE parent_id is not null AND deleted = 0 AND start_date >= %s AND start_date <= %s AND type != '' ORDER BY start_date ASC",
            $db->convert($db->quoted($startDate->asDbDate()), 'date'),
            $db->convert($db->quoted($endDate->asDbDate()), 'date')
        );

        $result = $db->query($query);
        if(!empty($result)) {
            while(($row = $db->fetchByAssoc($result))) {
               $timePeriods[$row['id']] = $row['name'];
            }
        }

        return $timePeriods;

    }

}