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
$viewdefs['ForecastWorksheets']['base']['view']['resolve-conflicts-list'] = array(
    'panels' => array(
        array(
            'label' => 'LBL_PANEL_1',
            'fields' => array(
                array(
                    'name' => 'commit_stage',
                    'type' => 'enum',
                    'searchBarThreshold' => 7,
                    'label' => 'LBL_FORECAST',
                    'sortable' => false,
                    'default' => true,
                    'enabled' => true,
                    'click_to_edit' => true
                ),
                array(
                    'name' => 'parent_name',
                    'label' => 'LBL_REVENUELINEITEM_NAME',
                    'link' => true,
                    'id' => 'parent_id',
                    'sortable' => true,
                    'default' => true,
                    'enabled' => true,
                    'display' => false,
                    'type' => 'parent',
                    'readonly' => true,
                ),
                array(
                    'name' => 'opportunity_name',
                    'label' => 'LBL_OPPORTUNITY_NAME',
                    'link' => true,
                    'id' => 'opportunity_id',
                    'id_name' => 'opportunity_id',
                    'module' => 'Opportunities',
                    'sortable' => true,
                    'default' => true,
                    'enabled' => true,
                    'type' => 'relate',
                    'readonly' => true
                ),
                array(
                    'name' => 'account_name',
                    'label' => 'LBL_ACCOUNT_NAME',
                    'link' => true,
                    'id' => 'account_id',
                    'id_name' => 'account_id',
                    'module' => 'Accounts',
                    'sortable' => true,
                    'default' => true,
                    'enabled' => true,
                    'type' => 'relate',
                    'readonly' => true
                ),
                array(
                    'name' => 'date_closed',
                    'label' => 'LBL_DATE_CLOSED',
                    'sortable' => true,
                    'default' => false,
                    'enabled' => true,
                    'type' => 'date',
                    'view' => 'detail',
                    'click_to_edit' => true
                ),
                array(
                    'name' => 'sales_stage',
                    'label' => 'LBL_SALES_STAGE',
                    'type' => 'enum',
                    'options' => 'sales_stage_dom',
                    'searchBarThreshold' => 7,
                    'sortable' => false,
                    'default' => false,
                    'enabled' => true,
                    'click_to_edit' => true
                ),
                array(
                    'name' => 'probability',
                    'label' => 'LBL_OW_PROBABILITY',
                    'type' => 'int',
                    'default' => false,
                    'enabled' => true,
                    'click_to_edit' => true,
                    'align' => 'right',
                    'width' => '7%'
                ),
                array(
                    'name' => 'likely_case',
                    'label' => 'LBL_LIKELY',
                    'type' => 'currency',
                    'default' => false,
                    'enabled' => true,
                    'convertToBase' => true,
                    'showTransactionalAmount' => true,
                    'align' => 'right',
                    'click_to_edit' => true,
                ),
                array(
                    'name' => 'best_case',
                    'label' => 'LBL_BEST',
                    'type' => 'currency',
                    'default' => false,
                    'enabled' => true,
                    'convertToBase' => true,
                    'showTransactionalAmount' => true,
                    'align' => 'right',
                    'click_to_edit' => true,
                ),
            ),
        ),
    ),
);
