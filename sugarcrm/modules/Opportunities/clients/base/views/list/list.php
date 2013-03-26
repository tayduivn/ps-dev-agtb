<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
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

/*********************************************************************************
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

$viewdefs['Opportunities']['base']['view']['list'] = array(
    'panels' => array(
        array(
            'name' => 'panel_header',
            'label' => 'LBL_PANEL_1',
            'fields' => array(
                array(
                    'name' => 'name',
                    'width' =>  30,
                    'link' => true,
                    'label' => 'LBL_LIST_OPPORTUNITY_NAME',
                    'enabled' => true,
                    'default' => true,
                ),
                array(
                    'name' => 'account_name',
                    'width' =>  20,
                    'link'    => true,
                    'label' => 'LBL_LIST_ACCOUNT_NAME',
                    'enabled' => true,
                    'default' => true,
                    'sortable' => false,
                ),
//BEGIN SUGARCRM flav=pro && flav!=ent ONLY
                array(
                    'name' => 'sales_stage',
                    'width' => 10,
                    'label' => 'LBL_LIST_SALES_STAGE',
                    'default' => true,
                ),
//END SUGARCRM flav=pro && flav!=ent ONLY
                'sales_status',
                array(
                    'name' => 'amount',
                    'type' => 'currency',
                    'label' => 'LBL_LIST_AMOUNT',
                    'related_fields' => array(
                        'amount',
                        'currency_id',
                        'base_rate',
                    ),
                    //BEGIN SUGARCRM flav=ent ONLY
                    'readonly' => true,
                    //END SUGARCRM flav=ent ONLY
                    'currency_field' => 'currency_id',
                    'base_rate_field' => 'base_rate',
                    'width' => 10
                ),
                array(
                    'name' => 'opportunity_type',
                    'width' => 15,
                    'label' => 'LBL_TYPE',
                ),
                array(
                    'name' => 'lead_source',
                    'width' => 15,
                    'label' => 'LBL_LEAD_SOURCE',
                ),
                array(
                    'name' => 'next_step',
                    'width' => 10,
                    'label' => 'LBL_NEXT_STEP',
                ),
                array(
                    'name' => 'probability',
                    'width' => 10,
                    'label' => 'LBL_PROBABILITY',
                ),
                array (
                    'name' => 'date_closed',
                    'width' => 10,
                    'label' => 'LBL_LIST_DATE_CLOSED',
                    'enabled' => true,
                    'default' => true,
                ),
                array(
                    'name' => 'created_by_name',
                    'width' => 10,
                    'label' => 'LBL_CREATED',
                    'sortable' => false,
                ),
//BEGIN SUGARCRM flav=pro ONLY
                array(
                    'name' => 'team_name',
                    'type' => 'teamset',
                    'width' => 5,
                    'label' => 'LBL_LIST_TEAM',
                    'default' => false,
                    'sortable' => false,
                ),
//END SUGARCRM flav=pro ONLY
                array (
                    'bwcLink' => true,
                    'module' => 'Employees',
                    'name' => 'assigned_user_name',
                    'width' => 5,
                    'label' => 'LBL_LIST_ASSIGNED_USER',
                    'id' => 'ASSIGNED_USER_ID',
                    'enabled' => true,
                    'default' => true,
                    'sortable' => false,
                ),
                array(
                    'name' => 'modified_by_name',
                    'width' => 5,
                    'label' => 'LBL_MODIFIED',
                    'sortable' => false,
                ),
                array(
                    'name' => 'date_entered',
                    'width' => 10,
                    'label' => 'LBL_DATE_ENTERED',
                    'default' => true,
                ),
            ),
        ),
    ),
);
