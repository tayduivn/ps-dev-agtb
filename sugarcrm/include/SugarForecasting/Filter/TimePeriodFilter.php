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

        $timePeriods = array();

        $current = TimePeriod::getCurrentTimePeriod($type);

        if(empty($current)) {
            return $timePeriods;
        }

        $iterator = $current;
        $found = array();
        while($backward-- > 0) {
            $previous = $iterator->getPreviousTimePeriod();
            if(!empty($previous)) {
                array_push($found, $previous);
                $iterator = $previous;
            }
        }

        while($tp = array_pop($found)) {
            $children = $tp->getLeaves();
            foreach($children as $tp) {
                $timePeriods[$tp->id] = $tp->name;
            }
        }

        //Add current timeperiods
        $children = $current->getLeaves();
        foreach($children as $tp) {
            $timePeriods[$tp->id] = $tp->name;
        }

        //Add future timeperiods
        $iterator = $current;
        while($forward-- > 0) {
            $next = $iterator->getNextTimePeriod();
            if(!empty($next)) {
                $children = $next->getLeaves();
                foreach($children as $tp) {
                    $timePeriods[$tp->id] = $tp->name;
                }
                $iterator = $next;
            }
        }

        return $timePeriods;
    }

}