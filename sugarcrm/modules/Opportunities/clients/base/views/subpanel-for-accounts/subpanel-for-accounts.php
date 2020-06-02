<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
$viewdefs['Opportunities']['base']['view']['subpanel-for-accounts'] = array(
    'type' => 'subpanel-list',
    'panels' => array(
        array(
            'name' => 'panel_header',
            'label' => 'LBL_PANEL_1',
            'fields' => array(
                array(
                    'name' => 'name',
                    'label' => 'LBL_LIST_OPPORTUNITY_NAME',
                    'enabled' => true,
                    'default' => true,
                    'link' => true,
                ),
                array(
                    'name' => 'probability',
                    'label' => 'LBL_PROBABILITY',
                    'enabled' => true,
                    'default' => true,
                ),
                // BEGIN SUGARCRM flav!=ent ONLY
                array(
                    'name' => 'date_closed',
                    'label' => 'LBL_DATE_CLOSED',
                    'enabled' => true,
                    'default' => true,
                    'related_fields' => array(
                        'date_closed_timestamp',
                    ),
                ),
                // END SUGARCRM flav!=ent ONLY
                // BEGIN SUGARCRM flav=ent ONLY
                array(
                    'name' => 'date_closed',
                    'type' => 'date-cascade',
                    'label' => 'LBL_DATE_CLOSED',
                    'enabled' => true,
                    'default' => true,
                    'disable_field' => array(
                        'total_revenue_line_items',
                        'closed_revenue_line_items',
                    ),
                    'related_fields' => [
                        'total_revenue_line_items',
                        'closed_revenue_line_items',
                    ],
                ),
                // END SUGARCRM flav=ent ONLY
                [
                    'name' => 'sales_status',
                    'readonly' => true,
                ],
                // BEGIN SUGARCRM flav!=ent ONLY
                array(
                    'name' => 'sales_stage',
                    'label' => 'LBL_LIST_SALES_STAGE',
                ),
                // END SUGARCRM flav!=ent ONLY
                // BEGIN SUGARCRM flav=ent ONLY
                array(
                    'name' => 'sales_stage',
                    'type' => 'enum-cascade',
                    'label' => 'LBL_LIST_SALES_STAGE',
                    'disable_field' => array(
                        'total_revenue_line_items',
                        'closed_revenue_line_items',
                    ),
                    'related_fields' => [
                        'total_revenue_line_items',
                        'closed_revenue_line_items',
                    ],
                ),
                array(
                    'name' => 'service_start_date',
                    'type' => 'date-cascade',
                    'label' => 'LBL_SERVICE_START_DATE',
                    'disable_field' => 'service_open_revenue_line_items',
                    'related_fields' => array(
                        'service_open_revenue_line_items',
                    ),
                ),
                // END SUGARCRM flav=ent ONLY
                array(
                    'name' => 'amount',
                    'type' => 'currency',
                    'label' => 'LBL_LIKELY',
                    'related_fields' => array(
                        'amount',
                        'currency_id',
                        'base_rate',
                    ),
                    'currency_field' => 'currency_id',
                    'base_rate_field' => 'base_rate',
                    'enabled' => true,
                    'default' => true,
                ),
                array(
                    'name' => 'assigned_user_name',
                    'target_record_key' => 'assigned_user_id',
                    'target_module' => 'Employees',
                    'label' => 'LBL_LIST_ASSIGNED_TO_NAME',
                    'enabled' => true,
                    'default' => true,
                ),
            ),
        ),
    ),
);
