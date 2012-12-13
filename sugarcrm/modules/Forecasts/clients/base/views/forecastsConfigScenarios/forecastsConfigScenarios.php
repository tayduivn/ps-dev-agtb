<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
$viewdefs['Forecasts']['base']['view']['forecastsConfigScenarios'] = array(
    'registerLabelAsBreadCrumb' => true,
    'panels' => array(
        array(
            'label' => 'LBL_FORECASTS_CONFIG_BREADCRUMB_SCENARIOS',
            'fields' => array(
                array(
                    'name' => 'show_worksheet_likely',
                    'type' => 'bool',
                    'label' => 'LBL_FORECASTS_CONFIG_WORKSHEET_SCENARIOS_LIKELY',
                    'default' => false,
                    'enabled' => true,
                    'view' => 'detail',
                ),
                array(
                    'name' => 'show_worksheet_best',
                    'type' => 'bool',
                    'label' => 'LBL_FORECASTS_CONFIG_WORKSHEET_SCENARIOS_BEST',
                    'default' => false,
                    'enabled' => true,
                    'view' => 'forecastsWorksheet',
                ),
                array(
                    'name' => 'show_worksheet_worst',
                    'type' => 'bool',
                    'label' => 'LBL_FORECASTS_CONFIG_WORKSHEET_SCENARIOS_WORST',
                    'default' => false,
                    'enabled' => true,
                    'view' => 'forecastsWorksheet',
                ),
            ),
        ),
        //TODO-sfa - this will be revisited in a future sprint and determined whether it should go in 6.7, 6.8 or later
        // BEGIN SUGARCRM flav=int ONLY
        array(
            'label' => 'LBL_FORECASTS_CONFIG_PROJECTED_SCENARIOS',
            'fields' => array(
                array(
                    'name' => 'show_projected_likely',
                    'type' => 'bool',
                    'label' => 'LBL_FORECASTS_CONFIG_PROJECTED_SCENARIOS_LIKELY',
                    'default' => false,
                    'enabled' => true,
                    'view' => 'detail',
                ),
                array(
                    'name' => 'show_projected_best',
                    'type' => 'bool',
                    'label' => 'LBL_FORECASTS_CONFIG_PROJECTED_SCENARIOS_BEST',
                    'default' => false,
                    'enabled' => true,
                    'view' => 'edit',
                ),
                array(
                    'name' => 'show_projected_worst',
                    'type' => 'bool',
                    'label' => 'LBL_FORECASTS_CONFIG_PROJECTED_SCENARIOS_WORST',
                    'default' => false,
                    'enabled' => true,
                    'view' => 'edit',
                ),
            ),
        ),
        // END SUGARCRM flav=int ONLY
    ),
);