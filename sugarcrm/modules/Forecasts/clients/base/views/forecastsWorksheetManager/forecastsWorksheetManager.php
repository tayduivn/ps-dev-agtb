<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement (""License"") which can be viewed at
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
 *  (i) the ""Powered by SugarCRM"" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

$viewdefs['Forecasts']['base']['view']['forecastsWorksheetManager'] = array(
    'panels' =>
    array(
        0 =>
        array(
            'label' => 'LBL_PANEL_1',
            'fields' =>
            array(

                array(
                    'name' => 'name',
                    'type' => 'userLink',
                    'label' => 'LBL_NAME',
                    'link' => true,
                    'route' =>
                    array(
                        'recordID'=>'user_id'
                    ),
                    'sortable' => true,
                    'default' => true,
                    'enabled' => true,
                ),

                array(
                    'name' => 'amount',
                    'type' => 'currency',
                    'label' => 'LBL_AMOUNT',
                    'sortable' => true,
                    'default' => true,
                    'enabled' => false,
                    'convertToBase' => true,
                ),

                array(
                    'name' => 'quota',
                    'type' => 'editableCurrency',
                    'label' => 'LBL_QUOTA',
                    'sortable' => true,
                    'default' => true,
                    'enabled' => true,
                    'convertToBase'=> true,
                ),

                array(
                    'name' => 'likely_case',
                    'type' => 'currency',
                    'label' => 'LBL_LIKELY_CASE',
                    'sortable' => true,
                    'default' => true,
                    'enabled' => true,
                    'convertToBase'=> true,
                ),

                array(
                    'name' => 'likely_case_adjusted',
                    'type' => 'editableCurrency',
                    'label' => 'LBL_LIKELY_CASE_VALUE',
                    'sortable' => true,
                    'default' => true,
                    'enabled' => true,
                    'convertToBase'=> true,
               ),

                array(
                    'name' => 'best_case',
                    'type' => 'currency',
                    'label' => 'LBL_BEST_CASE',
                    'sortable' => true,
                    'default' => true,
                    'enabled' => true,
                    'convertToBase'=> true,
                ),

                array(
                    'name' => 'best_case_adjusted',
                    'type' => 'editableCurrency',
                    'label' => 'LBL_BEST_CASE_VALUE',
                    'sortable' => true,
                    'default' => true,
                    'enabled' => true,
                    'convertToBase'=> true,
                ),

                array(
                    'name' => 'worst_case',
                    'type' => 'currency',
                    'label' => 'LBL_WORST_CASE',
                    'sortable' => true,
                    'default' => true,
                    'enabled' => true,
                    'convertToBase'=> true,
                ),

                array(
                    'name' => 'worst_case_adjusted',
                    'type' => 'editableCurrency',
                    'label' => 'LBL_WORST_CASE_VALUE',
                    'sortable' => true,
                    'default' => true,
                    'enabled' => true,
                    'convertToBase'=> true,
                ),

                array(
                    'name' => 'user_history_log',
                    'type' => 'commitLog',
                    'label' => '',
                    'sortable' => false,
                    'default' => true,
                    'enabled' => true,
               ),
            ),
        ),
    ),
);
