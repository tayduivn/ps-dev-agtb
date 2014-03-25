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
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 */

$dictionary['Dashboard'] = array(
    'table' => 'dashboards',
    'fields' => array(
        'dashboard_module' => array(
            'required' => false,
            'name' => 'dashboard_module',
            'vname' => 'LBL_DASHBOARD_MODULE',
            'type' => 'varchar',
            'dbType' => 'varchar',
            'len' => 100,
            'massupdate' => 0,
            'no_default' => false,
            'comments' => '',
            'help' => '',
            'importable' => true,
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => false,
            'reportable' => true,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'calculated' => false,
        ),
        'view_name' => array(
            'required' => false,
            'name' => 'view_name',
            'vname' => 'LBL_VIEW',
            'type' => 'varchar',
            'dbType' => 'varchar',
            'len' => 100,
            'massupdate' => 0,
            'no_default' => false,
            'comments' => '',
            'help' => '',
            'importable' => true,
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => false,
            'reportable' => true,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'calculated' => false,
        ),
        'metadata' => array(
            'required' => false,
            'name' => 'metadata',
            'vname' => 'LBL_METADATA',
            'type' => 'json',
            'dbType' => 'text',
            'massupdate' => 0,
            'no_default' => false,
            'comments' => '',
            'help' => '',
            'importable' => true,
            'duplicate_merge' => 'disabled',
            'duplicate_merge_dom_value' => '0',
            'audited' => false,
            'reportable' => true,
            'unified_search' => false,
            'merge_filter' => 'disabled',
            'calculated' => false,
        ),
        'dashboard_type' => array(
            'name' => 'dashboard_type',
            'vname' => 'LBL_DASHBOARD_TYPE',
            'type' => 'varchar',
            'len' => '100',
            'comment' => 'The type of dashboard: dashboard, help-dashboard, etc',
            'default' => 'dashboard'
        ),
    ),
    'indices' => array(
        array(
            'name' => 'user_module_view',
            'type' => 'index',
            'fields' => array('assigned_user_id', 'dashboard_module', 'view_name')
        ),
    ),
    'relationships' =>
        array(),
);

if (!class_exists('VardefManager')) {
    require_once('include/SugarObjects/VardefManager.php');
}
VardefManager::createVardef('Dashboards', 'Dashboard', array('basic', 'assignable'));
