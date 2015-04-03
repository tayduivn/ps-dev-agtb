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
                                    'facet_type' => 'assigned_to_me',
                                    'custom_toolbar' => 'no',
                                    'label' => 'LBL_ASSIGNED_TO_SELF',
                                ),
                            'width' => 12,
                        ),
                        array(
                            'view' =>
                                array(
                                    'type' => 'search-facet',
                                    'facet_type' => 'my_favorites',
                                    'custom_toolbar' => 'no',
                                    'label' => 'LBL_FAVORITES',
                                ),
                            'width' => 12,
                        ),
                        array(
                            'view' =>
                                array(
                                    'type' => 'search-facet',
                                    'facet_type' => 'created_by_me',
                                    'custom_toolbar' => 'no',
                                    'label' => 'LBL_CREATED_BY_ME',
                                ),
                            'width' => 12,
                        ),
                        array(
                            'view' =>
                                array(
                                    'type' => 'search-facet',
                                    'facet_type' => 'modified_by_me',
                                    'custom_toolbar' => 'no',
                                    'label' => 'LBL_MODIFIED_BY_ME',
                                ),
                            'width' => 12,
                        ),
                        array(
                            'view' =>
                                array(
                                    'type' => 'search-facet',
                                    'label' => 'LBL_FACET_MODULES',
                                    'facet_type' => 'modules',
                                    'custom_toolbar' => array(
                                        "buttons" => array(
                                            array(
                                                "type" => "dashletaction",
                                                "css_class" => "dashlet-toggle btn btn-invisible minify",
                                                "icon" => "fa-chevron-up",
                                                "action" => "toggleMinify",
                                                "tooltip" => "LBL_DASHLET_TOGGLE",
                                            ),
                                        ),
                                    ),
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
