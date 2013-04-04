<?php
//FILE SUGARCRM flav=pro || flav=sales ONLY
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
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
/*********************************************************************************
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

$viewdefs['Forecasts']['base']['view']['tutorial'] = array(
    'records' => array(
        'intro' =>'LBL_TOUR_FORECAST_INTRO',
        'version' =>1,
        'content' => array(
            array(
                'name' => '.view-forecastsTimeperiod b',
                'text' => 'LBL_TOUR_FORECASTS_TIMEPERIODS',
                'full' => true,
                'horizAdj'=> -15,
                'vertAdj'=> -15,
            ),
            array(
                'name' => '.view-forecastsCommitLog .icon-caret-down',
                'text' => 'LBL_TOUR_FORECASTS_COMMITS',
                'full' => true,
                'horizAdj'=> -20,
                'vertAdj'=> -20,
            ),
            array(
                'name' => '.td-inline-edit',
                'text' => 'LBL_TOUR_FORECASTS_INLINEEDIT',
                'full' => true,
            ),
            array(
                'name' => '.view-forecastsProgress h4',
                'text' => 'LBL_TOUR_FORECASTS_PROGRESS',
                'full' => true,
                'horizAdj'=> -1,
                'vertAdj'=> -5,
            ),
            array(
                'name' => '.view-forecastsChart h4',
                'text' => 'LBL_TOUR_FORECASTS_CHART',
                'full' => true,
                'horizAdj'=> -1,
                'vertAdj'=> -5,
            ),
        )
    ),
);
