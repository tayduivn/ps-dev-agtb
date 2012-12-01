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
    //Simulate settings to create 2 forward and 2 backward timeperiods
    $settings = array();
    $settings['timeperiod_start_month'] = 1;
    $settings['timeperiod_start_day'] = 1;
    $settings['timeperiod_interval'] = TimePeriod::ANNUAL_TYPE;
    $settings['timeperiod_leaf_interval'] = TimePeriod::QUARTER_TYPE;
    $settings['timeperiod_shown_backward'] = 2;
    $settings['timeperiod_shown_forward'] = 2;

    $timePeriod = TimePeriod::getByType(TimePeriod::ANNUAL_TYPE);
    $timePeriod->rebuildForecastingTimePeriods(array(), $settings);
    $ids = TimePeriod::get_not_fiscal_timeperiods_dom();
    $timeperiods = array();
    foreach($ids as $id=>$name) {
        $timeperiods[$id] = TimePeriod::getBean($id);
    }
    return $timeperiods;
}

}