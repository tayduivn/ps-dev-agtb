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
$viewdefs['Forecasts']['base']['layout']['config-drawer-content'] = array(
    'components' => array(
        // BEGIN SUGARCRM flav=int ONLY
        // todo-sfa - forecastBy will be revisited in a future release
        array(
            'view' => 'forecastsConfigForecastBy',
        ),
        // END SUGARCRM flav=int ONLY
        array(
            'view' => 'config-timeperiods',
        ),
        array(
            'view' => 'config-ranges',
        ),
        array(
            'view' => 'config-scenarios',
        ),
        array(
            'view' => 'config-worksheet-columns',
        ),
    ),
);
