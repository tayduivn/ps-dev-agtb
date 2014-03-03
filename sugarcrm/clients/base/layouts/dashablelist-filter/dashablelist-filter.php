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
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
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
                                'span' => 12,
                                'type' =>'filter',
                            ),
                            'targetEl' => '.filter',
                            'position' => 'prepend'
                        ),
                        array(
                            'view' => 'filter-rows',
                            'targetEl' => '.filter-options'
                        ),
                        array(
                            'view' => 'filter-actions',
                            'targetEl' => '.filter-options'
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
    'name' => 'dashablelist-filter',
    'span' => 12,
);
