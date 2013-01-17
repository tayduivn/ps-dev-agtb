<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

$viewdefs['base']['view']['audit'] = array(
    'type' => 'audit',
    'buttons' => array(
        array(
            'type' => 'button',
            'css_class' => 'pull-right btn btn-invisible drawer-close',
            'icon' => 'icon-remove',
            'primary' => false,
            'events' => array(
                'click' => 'function(e){
                    this.view.layout.trigger("audit:close_changelog");
                }',
            ),
        ),
    ),
    'panels' =>
    array(
        array(
            'fields' => array(
                array(
                    'type' => 'base',
                    'name' => 'field_name',
                    'label' => 'LBL_FIELD_NAME',
                ),
                array(
                    'type' => 'base',
                    'name' => 'before_value_string',
                    'label' => 'LBL_OLD_NAME',
                ),
                array(
                    'type' => 'base',
                    'name' => 'after_value_string',
                    'label' => 'LBL_NEW_VALUE',
                ),
                array(
                    'type' => 'base',
                    'name' => 'created_by',
                    'label' => 'LBL_CREATED_BY',
                    ),
                array(
                    'type' => 'datetime',
                    'name' => 'date_created',
                    'label' => 'LBL_LIST_DATE',
                ),
                array(
                    'type' => 'base',
                    'name' => 'data_type',
                ),
            ),
        ),
    ),
);
