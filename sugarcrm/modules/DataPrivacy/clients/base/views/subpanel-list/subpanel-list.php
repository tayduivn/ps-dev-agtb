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
$viewdefs['DataPrivacy']['base']['view']['subpanel-list'] = array(
    'panels' =>
        array(
            array(
                'name' => 'panel_header',
                'label' => 'LBL_PANEL_1',
                'fields' =>
                    array(
                        array(
                            'label' => 'LBL_LIST_NUMBER',
                            'enabled' => true,
                            'default' => true,
                            'readonly' => true,
                            'name' => 'dataprivacy_number',
                        ),
                        array(
                            'label' => 'LBL_LIST_SUBJECT',
                            'enabled' => true,
                            'default' => true,
                            'name' => 'name',
                            'link' => true,
                        ),
                        array(
                            'label' => 'LBL_LIST_TYPE',
                            'enabled' => true,
                            'default' => true,
                            'name' => 'type',
                        ),
                        array(
                            'label' => 'LBL_LIST_PRIORITY',
                            'enabled' => true,
                            'default' => true,
                            'name' => 'priority',
                        ),
                        array(
                            'label' => 'LBL_LIST_STATUS',
                            'enabled' => true,
                            'default' => true,
                            'name' => 'status',
                        ),
                        array(
                            'label' => 'LBL_LIST_SOURCE',
                            'enabled' => true,
                            'default' => true,
                            'name' => 'source',
                        ),
                        array(
                            'label' => 'LBL_LIST_DATE_DUE',
                            'enabled' => true,
                            'default' => true,
                            'name' => 'date_due',
                        ),
                        array(
                            'label' => 'LBL_LIST_DATE_RESOLVED',
                            'enabled' => true,
                            'default' => true,
                            'name' => 'date_resolved',
                        ),
                        array(
                            'label' => 'LBL_LIST_DATE_MODIFIED',
                            'enabled' => true,
                            'default' => true,
                            'name' => 'date_modified',
                        ),
                        array(
                            'name' => 'modified_by_name',
                            'target_record_key' => 'modified_user_id',
                            'target_module' => 'Employees',
                            'label' => 'LBL_LIST_MODIFIED_BY_NAME',
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
                        array(
                            'label' => 'LBL_LIST_REQUESTED_BY',
                            'enabled' => true,
                            'default' => true,
                            'name' => 'requested_by',
                        ),
                    ),
            ),
        ),
);
