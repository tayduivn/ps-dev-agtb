<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */
$viewdefs['Opportunities']['base']['view']['dupecheck-list'] = array(
    'panels' => array(
        array(
            'name' => 'panel_header',
            'label' => 'LBL_PANEL_1',
            'fields' => array(
                array(
                    'name' => 'name',
                    'width' => 30,
                    'link' => true,
                    'label' => 'LBL_LIST_OPPORTUNITY_NAME',
                    'enabled' => true,
                    'default' => true,
                    'related_fields' => array(
                        'total_revenue_line_items',
                        'closed_revenue_line_items',
                    ),
                ),
                array(
                    'name' => 'account_name',
                    'width' => 20,
                    'link' => true,
                    'label' => 'LBL_LIST_ACCOUNT_NAME',
                    'enabled' => true,
                    'default' => true,
                    'sortable' => false,
                ),
                array(
                    'name' => 'sales_status',
                    'enabled' => true,
                    'default' => true,
                    'readonly' => true,
                ),
                array(
                    'name' => 'amount',
                    'type' => 'currency',
                    'label' => 'LBL_LIKELY',
                    'related_fields' => array(
                        'amount',
                        'currency_id',
                        'base_rate',
                    ),
                    'readonly' => true,
                    'currency_field' => 'currency_id',
                    'base_rate_field' => 'base_rate',
                    'width' => 10,
                    'enabled' => true,
                    'default' => true,
                ),
                array(
                    'name' => 'opportunity_type',
                    'width' => 15,
                    'label' => 'LBL_TYPE',
                    'enabled' => true,
                    'default' => false,
                ),
                array(
                    'name' => 'lead_source',
                    'width' => 15,
                    'label' => 'LBL_LEAD_SOURCE',
                    'enabled' => true,
                    'default' => false,
                ),
                array(
                    'name' => 'next_step',
                    'width' => 10,
                    'label' => 'LBL_NEXT_STEP',
                    'enabled' => true,
                    'default' => false,
                ),
                array(
                    'name' => 'date_closed',
                    'width' => 10,
                    'label' => 'LBL_DATE_CLOSED',
                    'enabled' => true,
                    'default' => false,
                    'readonly' => true
                ),
                array(
                    'name' => 'created_by_name',
                    'width' => 10,
                    'label' => 'LBL_CREATED',
                    'sortable' => false,
                    'enabled' => true,
                    'default' => false,
                    'readonly' => true,
                ),
                array(
                    'name' => 'team_name',
                    'type' => 'teamset',
                    'width' => 5,
                    'label' => 'LBL_LIST_TEAM',
                    'enabled' => true,
                    'default' => false,
                    'sortable' => false,
                ),
                array(
                    'name' => 'assigned_user_name',
                    'width' => 5,
                    'label' => 'LBL_LIST_ASSIGNED_USER',
                    'id' => 'ASSIGNED_USER_ID',
                    'enabled' => true,
                    'default' => false,
                    'sortable' => false,
                ),
                array(
                    'name' => 'modified_by_name',
                    'width' => 5,
                    'label' => 'LBL_MODIFIED',
                    'sortable' => false,
                    'enabled' => true,
                    'default' => false,
                    'readonly' => true,
                ),
                array(
                    'name' => 'date_entered',
                    'width' => 10,
                    'label' => 'LBL_DATE_ENTERED',
                    'enabled' => true,
                    'default' => false,
                    'readonly' => true,
                ),
            ),
        ),
    ),
);
