<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
$viewdefs['NotificationCenter']['base']['layout']['config-drawer-content'] = array(
    // All components except the first one are generated dynamically.
    // See NotificationCenterConfigDrawerContentLayout::_createViews.
    'components' => array(
        array(
            'view' => array(
                'name' => 'config-carriers',
                'type' => 'config-carriers',
                'label' => 'LBL_CARRIER_DELIVERY_OPTION_TITLE',
            ),
        ),
    ),
);
