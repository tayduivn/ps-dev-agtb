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
 * Copyright (C) 2004-2013 SugarCRM Inc. All rights reserved.
 */
$subpanel_layout = array(
    'top_buttons' => array(
       array('widget_class' => 'SubPanelTopCreateButton'),
       array('widget_class' => 'SubPanelTopSelectButton', 'popup_module' => 'Emails'),
    ),
    'where' => '',
    'list_fields' => array(
        'name' => array(
            'name' => 'name',
            'vname' => 'LBL_LIST_SUBJECT',
            'width' => '20%',
        ),
        'status'=> array(
            'name' => 'status',
            'vname' => 'LBL_LIST_STATUS',
            'width' => '20%',
        ),
        'date_entered'=>array(
            'name' => 'date_entered',
            'vname' => 'LBL_DATE_CREATED',
            'width' => '20%',
        ),
        'date_modified' => array(
            'name' => 'date_modified',
            'vname' => 'LBL_DATE_MODIFIED',
            'width' => '20%',
        ),
        'assigned_user_name' => array (
            'name' => 'assigned_user_name',
            'vname' => 'LBL_ASSIGNED_USER',
            'widget_class' => 'SubPanelDetailViewLink',
            'target_record_key' => 'assigned_user_id',
            'target_module' => 'Employees',
        ),
    ),
);
