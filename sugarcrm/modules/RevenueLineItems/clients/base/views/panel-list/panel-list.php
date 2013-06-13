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
$viewdefs['RevenueLineItems']['base']['view']['panel-list'] = array(
    'favorite' => true,
    'selection' => array(
        'type' => 'multi',
        'actions' => array(
            array(
                'name' => 'edit_button',
                'type' => 'button',
                'label' => 'LBL_MASS_UPDATE',
                'primary' => true,
                'events' => array(
                    'click' => 'function(e){
                    this.view.layout.trigger("list:massupdate:fire");
                    }'
                ),
                'acl_action' => 'massupdate',
            ),
            array(
                'name' => 'quote_button',
                'type' => 'button',
                'label' => 'LBL_CREATE_QUOTE',
                'primary' => true,
                'events' => array(
                    'click' => 'function(e){
                    this.view.layout.trigger("list:massquote:fire");
                    }'
                ),
                'acl_action' => 'massquote',
            ),
            array(
                'name' => 'delete_button',
                'type' => 'button',
                'label' => 'LBL_DELETE',
                'acl_action' => 'delete',
                'primary' => true,
                'events' => array(
                    'click' => 'function(e){
                    this.view.layout.trigger("list:massdelete:fire");
                    }'
                ),
                'acl_action' => 'delete',
            ),
            array(
                'name' => 'export_button',
                'type' => 'button',
                'label' => 'LBL_EXPORT',
                'acl_action' => 'export',
                'primary' => true,
                'events' => array(
                    'click' => 'function(e){
                    this.view.layout.trigger("list:massexport:fire");
                    }'
                ),
            ),
        ),
    ),
    'rowactions' => array(
        'css_class' => 'pull-right',
        'actions' => array(
            array(
                'type' => 'rowaction',
                'css_class' => 'btn',
                'tooltip' => 'LBL_PREVIEW',
                'event' => 'list:preview:fire',
                'icon' => 'icon-eye-open',
                'acl_action' => 'view',
            ),
            array(
                'type' => 'rowaction',
                'name' => 'edit_button',
                'icon' => 'icon-pencil',
                'label' => 'LBL_EDIT_BUTTON',
                'event' => 'list:editrow:fire',
                'acl_action' => 'edit',
            ),
        ),
    ),
);
