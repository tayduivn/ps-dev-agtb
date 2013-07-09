<?php
//FILE SUGARCRM flav=pro || flav=sales ONLY
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
$viewdefs['ProspectLists']['base']['view']['record'] = array(
    'buttons' => array(
        array(
            'type'      => 'button',
            'name'      => 'cancel_button',
            'label'     => 'LBL_CANCEL_BUTTON_LABEL',
            'css_class' => 'btn-invisible btn-link',
            'showOn'    => 'edit',
        ),
        array(
            'type'       => 'rowaction',
            'event'      => 'button:save_button:click',
            'name'       => 'save_button',
            'label'      => 'LBL_SAVE_BUTTON_LABEL',
            'css_class'  => 'btn btn-primary',
            'showOn'     => 'edit',
            'acl_action' => 'edit',
        ),
        array(
            'type'    => 'actiondropdown',
            'name'    => 'main_dropdown',
            'primary' => true,
            'showOn'  => 'view',
            'buttons' => array(
                array(
                    'type'       => 'rowaction',
                    'event'      => 'button:edit_button:click',
                    'name'       => 'edit_button',
                    'label'      => 'LBL_EDIT_BUTTON_LABEL',
                    'acl_action' => 'edit',
                ),
                array(
                    'type'       => 'linkbutton',
                    'name'       => 'link_create',
                    'label'      => 'LBL_CREATE_RELATED_RECORD',
                    'acl_action' => 'edit',
                ),
                array(
                    'type'       => 'linkbutton',
                    'name'       => 'link_exist',
                    'label'      => 'LBL_ASSOC_RELATED_RECORD',
                    'acl_action' => 'edit',
                ),
                array(
                    'type'       => 'rowaction',
                    'event'      => 'button:delete_button:click',
                    'name'       => 'delete_button',
                    'label'      => 'LBL_DELETE_BUTTON_LABEL',
                    'acl_action' => 'delete',
                ),
                array(
                    'type'       => 'rowaction',
                    'event'      => 'button:duplicate_button:click',
                    'name'       => 'duplicate_button',
                    'label'      => 'LBL_DUPLICATE_BUTTON_LABEL',
                    'acl_action' => 'create',
                ),
                array(
                    'type'       => 'rowaction',
                    'event'      => 'button:export_button:click',
                    'name'       => 'export_button',
                    'label'      => 'LBL_EXPORT',
                    'acl_action' => 'export',
                ),
            ),
        ),
        array(
            'name' => 'sidebar_toggle',
            'type' => 'sidebartoggle',
        ),
    ),
    'panels'  => array(
        array(
            'name'   => 'panel_header',
            'header' => true,
            'fields' => array(
                'name',
                array(
                    'type'     => 'favorite',
                    'readonly' => true,
                ),
                array(
                    'type'     => 'follow',
                    'readonly' => true,
                ),
            ),
        ),
        array(
            'name'         => 'panel_body',
            'columns'      => 2,
            'labelsOnTop'  => true,
            'placeholders' => true,
            'fields'       => array(
                array(
                    'name' => 'description',
                    'span' => 12,
                ),
                array(
                    'name' => 'list_type',
                    'span' => 12,
                ),
                'assigned_user_name',
                array(
                    'name'     => 'date_modified_by',
                    'readonly' => true,
                    'type'     => 'fieldset',
                    'label'    => 'LBL_DATE_MODIFIED',
                    'fields'   => array(
                        array(
                            'name' => 'date_modified',
                        ),
                        array(
                            'type'          => 'label',
                            'default_value' => 'LBL_BY',
                        ),
                        array(
                            'name' => 'modified_by_name',
                        ),
                    ),
                ),
                //BEGIN SUGARCRM flav=pro ONLY
                array(
                    "type" => "teamset",
                    "name" => "team_name",
                ),
                //END SUGARCRM flav=pro ONLY
                array(
                    'name'     => 'date_entered_by',
                    'readonly' => true,
                    'type'     => 'fieldset',
                    'label'    => 'LBL_DATE_ENTERED',
                    'fields'   => array(
                        array(
                            'name' => 'date_entered',
                        ),
                        array(
                            'type'          => 'label',
                            'default_value' => 'LBL_BY',
                        ),
                        array(
                            'name' => 'created_by_name',
                        ),
                    ),
                ),
            ),
        ),
    ),
);
