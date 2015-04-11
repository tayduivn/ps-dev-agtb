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

$viewdefs['Home']['base']['layout']['search-dashboard'] = array(
    'metadata' => array(
        'components' => array(
            array(
                'rows' => array(
                    array(
                        array(
                            'view' =>
                                array(
                                    'type' => 'search-facet',
                                    'label' => 'LBL_FACET_MODULES',
                                    'name' => 'module-facet',
                                    'facet-type' => 'modules',
                                ),
                            'width' => 12,
                        ),
                        array(
                            'view' =>
                                array(
                                    'type' => 'search-facet',
                                    'label' => 'LBL_FACET_MODULES2',
                                    'name' => 'module-facet',
                                    'facet-type' => 'modules',
                                ),
                            'width' => 12,
                        ),
                    ),
                ),
                'width' => 12,
            ),
        ),
    ),
    'dashboard_type' => 'search-dashboard',
    'name' => 'LBL_FACETS_DASHBOARD_TITLE',
);
