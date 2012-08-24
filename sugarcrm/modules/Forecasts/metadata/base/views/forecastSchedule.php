<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

/**
 * forecastSchedule.php
 *
 * This file defines the view definitions for the forecastSchedule view.  The forecastSchedule
 * view uses the /ForecastSchedule REST endpoint to retrieve and save data.
 */
$viewdefs['Forecasts']['base']['view']['forecastSchedule'] = array(
    'panels' =>
    array(
        0 =>
        array(
            'label' => 'LBL_PANEL_1',
            'fields' =>
            array(
                array(
                    'name' => 'include_expected',
                    'type' => 'bool',
                    'label' => 'LBL_INCLUDE_EXPECTED',
                    'default' => true,
                    'enabled' => true
                ),
                array(
                    'name' => 'expected_commit_stage',
                    'type' => 'enum',
                    'options' => 'commit_stage_dom',
                    'label' => 'LBL_FORECAST',
                    'default' => true,
                    'enabled' => true,
                ),
                array(
                    'name' => 'expected_amount',
                    'label' => 'LBL_EXPECTED_AMOUNT',
                    'type' => 'currency',
                    'default' => true,
                    'enabled' => true,
                    'clickToEdit' => true
                ),
                array(
                    'name' => 'expected_best_case',
                    'label' => 'LBL_EXPECTED_BEST_CASE',
                    'type' => 'currency',
                    'default' => true,
                    'enabled' => true,
                    'clickToEdit' => true
                ),
                array(
                    'name' => 'expected_likely_case',
                    'label' => 'LBL_EXPECTED_LIKELY_CASE',
                    'type' => 'currency',
                    'default' => true,
                    'enabled' => true,
                    'clickToEdit' => true
                ),
                array(
                    'name' => 'expected_worst_case',
                    'label' => 'LBL_EXPECTED_WORST_CASE',
                    'type' => 'currency',
                    'default' => true,
                    'enabled' => false,
                    'clickToEdit' => true
                ),
            ),
        ),
    ),
);
