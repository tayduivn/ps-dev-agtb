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

$viewdefs['base']['layout']['dashablelist-filter'] = array(
    'components' => array(
        array(
            'layout' => array(
                'type' =>'filterpanel',
                'meta' => array(
                    'components' => array(
                        array(
                            'layout' => array(
                                'meta' => array(
                                    'components' => array(
                                        array(
                                            'view' => 'filter-filter-dropdown'
                                        ),
                                    ),
                                    'last_state' => array(
                                        'id' => 'filter',
                                    ),
                                    'layoutType' => 'records',
                                ),
                                'type' =>'filter',
                            ),
                        ),
                        array(
                            'view' => 'filter-rows',
                        ),
                        array(
                            'view' => 'filter-actions',
                        ),
                    ),
                    'filter_options' => array(
                        'auto_apply' => false,
                        'stickiness' => false,
                        'show_actions' => false,
                    ),
                ),
            ),
        ),
    ),
);
