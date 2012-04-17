<?php
//FILE SUGARCRM flav=pro || flav=sales ONLY
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
$viewdefs['Opportunities']['mobile']['view']['list'] = array(
    'panels' => array(
        array(
            'label' => 'LBL_PANEL_1',
            'fields' => array(
                array(
                    'name' => 'name',
                    'width' => '30',
                    'label' => 'LBL_LIST_OPPORTUNITY_NAME',
                    'link' => true,
                    'default' => true,
                    'enabled' => true
                ),
                array(
                    'name' => 'sales_stage',
                    'width' => '10',
                    'label' => 'LBL_LIST_SALES_STAGE',
                    'default' => true,
                    'enabled' => true
                ),
                array(
                    'name' => 'amount_usdollar',
                    'width' => '10',
                    'label' => 'LBL_LIST_AMOUNT_USDOLLAR',
                    'align' => 'right',
                    'default' => true,
                    'enabled' => true,
                    'currency_format' => true,
                ),
                array(
                    'name' => 'opportunity_type',
                    'width' => '15',
                    'label' => 'LBL_TYPE',
                    'default' => false,
                ),
                array(
                    'name' => 'lead_source',
                    'width' => '15',
                    'label' => 'LBL_LEAD_SOURCE',
                    'default' => false,
                ),
                array(
                    'name' => 'next_step',
                    'width' => '10',
                    'label' => 'LBL_NEXT_STEP',
                    'default' => false,
                ),
                array(
                    'name' => 'probability',
                    'width' => '10',
                    'label' => 'LBL_PROBABILITY',
                    'default' => false,
                ),
                array(
                    'name' => 'date_closed',
                    'width' => '10',
                    'label' => 'LBL_LIST_DATE_CLOSED',
                    'default' => true,
                    'enabled' => true
                ),
                array(
                    'name' => 'date_entered',
                    'width' => '10',
                    'label' => 'LBL_DATE_ENTERED',
                    'default' => false,
                ),
                array(
                    'name' => 'created_by_name',
                    'width' => '10',
                    'label' => 'LBL_CREATED',
                    'default' => false,
                ),
//BEGIN SUGARCRM flav=pro ONLY
                array(
                    'name' => 'team_name',
                    'width' => '5',
                    'label' => 'LBL_LIST_TEAM',
                    'default' => true,
                    'enabled' => true
                ),
//END SUGARCRM flav=pro ONLY
                array(
                    'name' => 'assigned_user_name',
                    'width' => '5',
                    'label' => 'LBL_LIST_ASSIGNED_USER',
                    'default' => true,
                    'enabled' => true
                ),
                array(
                    'name' => 'modified_by_name',
                    'width' => '5',
                    'label' => 'LBL_MODIFIED',
                    'default' => false,
                )
            )
        )
    )
);

?>
