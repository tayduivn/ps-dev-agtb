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
 * Copyright 2004-2013 SugarCRM Inc. All rights reserved.
 */

$viewdefs['Prospects']['base']['view']['panel-top-for-prospectlists'] = array(
    'type' => 'panel-top',
    'buttons' => array(
        array(
            'type' => 'button',
            'css_class' => 'btn-invisible',
            'icon' => 'icon-chevron-up',
            'tooltip' => 'LBL_TOGGLE_VISIBILITY',
        ),
        array(
            'type' => 'actiondropdown',
            'name' => 'panel_dropdown',
            'css_class' => 'pull-right',
            'buttons' => array(
                array(
                    'type' => 'sticky-rowaction',
                    'icon' => 'icon-plus',
                    'name' => 'create_button',
                    'label' => ' ',
                    'acl_action' => 'create',
                    'tooltip' => 'LBL_CREATE_BUTTON_LABEL',
                ),
                array(
                    'type' => 'link-action',
                    'name' => 'select_button',
                    'label' => 'LBL_ASSOC_RELATED_RECORD',
                ),
                array(
                    'type' => 'linkfromreportbutton',
                    'name' => 'select_button',
                    'label' => 'LBL_SELECT_REPORTS_BUTTON_LABEL',
                    'initial_filter' => 'by_module',
                    'initial_filter_label' => 'LBL_FILTER_PROSPECTS_REPORTS',
                    'filter_populate' => array(
                        'module' => array('Prospects'),
                    ),
                ),
            ),
        ),
    ),
);
