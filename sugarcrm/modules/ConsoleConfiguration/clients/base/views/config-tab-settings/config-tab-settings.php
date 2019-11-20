<?php
// FILE SUGARCRM flav=ent ONLY
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
$viewdefs['ConsoleConfiguration']['base']['view']['config-tab-settings'] = array(
    'label' => 'LBL_MODULE_NAME',
    'panels' => array(
        array(
            'label' => 'LBL_CONSOLE_SORT_ORDER_DEFAULT',
            'fields' => array(
                array(
                    'name' => 'order_by_primary',
                    'vname' => 'LBL_CONSOLE_SORT_ORDER_PRIMARY',
                    'type' => 'enum',
                ),
                array(
                    'name' => 'order_by_secondary',
                    'label' => 'LBL_CONSOLE_SORT_ORDER_SECONDARY',
                    'type' => 'enum',
                ),
            ),
        ),
        array(
            'label' => 'LBL_CONSOLE_FILTER',
            'fields' => array(
                array(
                    'name' => 'filter_def',
                    'vname' => 'LBL_CONSOLE_FILTER_RULES',
                    'type' => 'filter-field',
                ),
            ),
        ),
    ),
);
