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
$viewdefs['Forecasts']['base']['view']['forecastsConfigRanges'] = array(
    'registerLabelAsBreadCrumb' => true,
    'panels' => array(
        array(
            'label' => 'LBL_FORECASTS_CONFIG_BREADCRUMB_RANGES',
            'fields' => array(
                array(
                    'name' =>'forecast_ranges',
                    'type' => 'radioenum',
                    'label' => 'LBL_FORECASTS_CONFIG_RANGES_OPTIONS',
                    'view' => 'edit',
                    'options' => 'forecasts_config_ranges_options_dom',
                    'default' => false,
                    'enabled' => true,
                ),
                array(
                    'name' => 'category_ranges',
                    'ranges' => array(
                        array(
                            'name' => 'include',
                            'type' => 'range',
                            'view' => 'edit',
                            'sliderType' => 'connected',
                            'minRange' => 0,
                            'maxRange' => 100,
                            'default' => true,
                            'enabled' => true,
                        ),
                        array(
                            'name' => 'upside',
                            'type' => 'range',
                            'view' => 'edit',
                            'sliderType' => 'connected',
                            'minRange' => 0,
                            'maxRange' => 100,
                            'default' => true,
                            'enabled' => true,
                        ),
// TODO-sfa: 6.8 - SFA-196: implement custom buckets
//BEGIN SUGARCRM flav=ent ONLY
                        array(
                            'name' => 'custom_default',
                            'type' => 'range',
                            'view' => 'edit',
                            'sliderType' => 'connected',
                            'minRange' => 0,
                            'maxRange' => 100,
                            'default' => true,
                            'enabled' => true,
                        ),
                        array(
                            'name' => 'custom',
                            'type' => 'range',
                            'view' => 'edit',
                            'sliderType' => 'connected',
                            'minRange' => 0,
                            'maxRange' => 100,
                            'default' => true,
                            'enabled' => true,
                        ),
                        array(
                            'name' => 'custom_without_probability',
                            'type' => 'range',
                            'view' => 'edit',
                            'sliderType' => 'connected',
                            'minRange' => 0,
                            'maxRange' => 100,
                            'default' => true,
                            'enabled' => true,
                        ),
//END SUGARCRM flav=ent ONLY
                    ),
                ),
                array(
                    'name' => 'buckets_dom',
                    'options' => array(
                        'show_binary' => 'commit_stage_binary_dom',
                        'show_buckets' => 'commit_stage_dom',
// TODO-sfa: 6.8 - SFA-196: implement custom buckets
//BEGIN SUGARCRM flav=ent ONLY
                        'show_custom_buckets' => 'commit_stage_custom_dom'
//END SUGARCRM flav=ent ONLY
                    )
                )
            )
        ),
    ),
);
