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
 * Copyright  2004-2014 SugarCRM Inc.  All rights reserved.
 */

$viewdefs['base']['layout']['help-dashboard'] = array(
    'name' => 'LBL_DEFAULT_HELP_DASHBOARD_TITLE',
    'type' => 'help-dashboard',
    'dashboard_type' => 'help-dashboard',
    'metadata' => array(
        'components' => array(
            array(
                'rows' => array(
                    array(
                        array(
                            'view' => array(
                                'type' => 'help-dashlet',
                                'label' => 'LBL_DEFAULT_HELP_DASHLET_TITLE',
                            ),
                            'width' => 12,
                        ),
                    ),
                ),
                'width' => 12,
            ),
        ),
    ),
);
