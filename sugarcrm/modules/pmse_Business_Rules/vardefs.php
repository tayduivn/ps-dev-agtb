<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

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


$dictionary['pmse_Business_Rules'] = array(
    'table'=>'pmse_business_rules',
    'audited'=>false,
    'activity_enabled'=>true,
    'duplicate_merge'=>true,
    'fields'=>array (
        'name' =>
            array (
                'name' => 'name',
                'vname' => 'LBL_NAME',
                'type' => 'name',
                'dbType' => 'varchar',
                'len' => '255',
                'unified_search' => false,
                'required' => true,
                'importable' => 'required',
                'duplicate_merge' => 'enabled',
                'merge_filter' => 'selected',
                'duplicate_on_record_copy' => 'always',
                'massupdate' => false,
                'no_default' => false,
                'comments' => '',
                'help' => '',
                'duplicate_merge_dom_value' => '3',
                'audited' => false,
                'reportable' => true,
                'calculated' => false,
                'size' => '20',
            ),
        'rst_uid' =>
            array (
                'required' => true,
                'name' => 'rst_uid',
                'vname' => 'LBL_RST_UID',
                'type' => 'id',
                'massupdate' => false,
                'no_default' => false,
                'comments' => '',
                'help' => '',
                'importable' => 'true',
                'duplicate_merge' => 'disabled',
                'duplicate_merge_dom_value' => '0',
                'audited' => false,
                'reportable' => true,
                'unified_search' => false,
                'merge_filter' => 'disabled',
                'calculated' => false,
                'size' => '20',
            ),
        'rst_type' =>
            array (
                'required' => true,
                'name' => 'rst_type',
                'vname' => 'LBL_RST_TYPE',
                'type' => 'enum',
                'massupdate' => true,
                'default' => 'single',
                'no_default' => false,
                'comments' => '',
                'help' => '',
                'importable' => 'true',
                'duplicate_merge' => 'disabled',
                'duplicate_merge_dom_value' => '0',
                'audited' => false,
                'reportable' => true,
                'unified_search' => false,
                'merge_filter' => 'disabled',
                'calculated' => false,
                'len' => 100,
                'size' => '20',
                'options' => 'business_rule_type_list',
                'studio' => 'visible',
                'dependency' => false,
            ),
        'rst_definition' =>
            array (
                'required' => false,
                'name' => 'rst_definition',
                'vname' => 'LBL_RST_DEFINITION',
                'type' => 'text',
                'massupdate' => false,
                'no_default' => false,
                'comments' => '',
                'help' => '',
                'importable' => 'true',
                'duplicate_merge' => 'disabled',
                'duplicate_merge_dom_value' => '0',
                'audited' => false,
                'reportable' => true,
                'unified_search' => false,
                'merge_filter' => 'disabled',
                'calculated' => false,
                'size' => '20',
                'studio' => 'visible',
                'rows' => '4',
                'cols' => '20',
            ),
        'rst_editable' =>
            array (
                'required' => false,
                'name' => 'rst_editable',
                'vname' => 'LBL_RST_EDITABLE',
                'type' => 'int',
                'massupdate' => false,
                'default' => '0',
                'no_default' => false,
                'comments' => '',
                'help' => '',
                'importable' => 'true',
                'duplicate_merge' => 'disabled',
                'duplicate_merge_dom_value' => '0',
                'audited' => false,
                'reportable' => true,
                'unified_search' => false,
                'merge_filter' => 'disabled',
                'calculated' => false,
                'len' => '4',
                'size' => '20',
                'enable_range_search' => false,
                'disable_num_format' => '',
                'min' => false,
                'max' => false,
            ),
        'rst_source' =>
            array (
                'required' => false,
                'name' => 'rst_source',
                'vname' => 'LBL_RST_SOURCE',
                'type' => 'varchar',
                'massupdate' => false,
                'no_default' => false,
                'comments' => '',
                'help' => '',
                'importable' => 'true',
                'duplicate_merge' => 'disabled',
                'duplicate_merge_dom_value' => '0',
                'audited' => false,
                'reportable' => true,
                'unified_search' => false,
                'merge_filter' => 'disabled',
                'calculated' => false,
                'len' => '255',
                'size' => '20',
            ),
        'rst_source_definition' =>
            array (
                'required' => false,
                'name' => 'rst_source_definition',
                'vname' => 'LBL_RST_SOURCE_DEFINITION',
                'type' => 'text',
                'massupdate' => false,
                'no_default' => false,
                'comments' => '',
                'help' => '',
                'importable' => 'true',
                'duplicate_merge' => 'disabled',
                'duplicate_merge_dom_value' => '0',
                'audited' => false,
                'reportable' => true,
                'unified_search' => false,
                'merge_filter' => 'disabled',
                'calculated' => false,
                'size' => '20',
                'studio' => 'visible',
                'rows' => '4',
                'cols' => '20',
            ),
        'rst_module' =>
            array (
                'required' => true,
                'name' => 'rst_module',
                'vname' => 'LBL_RST_MODULE',
                'type' => 'enum',
                'massupdate' => true,
                'no_default' => false,
                'comments' => '',
                'help' => '',
                'importable' => 'true',
                'duplicate_merge' => 'disabled',
                'duplicate_merge_dom_value' => '0',
                'audited' => false,
                'reportable' => true,
                'unified_search' => false,
                'merge_filter' => 'disabled',
                'calculated' => false,
                'len' => 100,
                'size' => '20',
                'options' => '',
                'studio' => 'visible',
                'dependency' => false,
                'function' =>
                    array (
                        'name' => 'getTargetsModules',
                        'include' => 'modules/pmse_Project/pmse_ProjectHelper.php',
                    ),
            ),
        'rst_filename' =>
            array (
                'required' => false,
                'name' => 'rst_filename',
                'vname' => 'LBL_RST_FILENAME',
                'type' => 'varchar',
                'massupdate' => false,
                'no_default' => false,
                'comments' => '',
                'help' => '',
                'importable' => 'true',
                'duplicate_merge' => 'disabled',
                'duplicate_merge_dom_value' => '0',
                'audited' => false,
                'reportable' => true,
                'unified_search' => false,
                'merge_filter' => 'disabled',
                'calculated' => false,
                'len' => '255',
                'size' => '20',
            ),
        'rst_create_date' =>
            array (
                'required' => false,
                'name' => 'rst_create_date',
                'vname' => 'LBL_RST_CREATE_DATE',
                'type' => 'datetimecombo',
                'massupdate' => true,
                'no_default' => false,
                'comments' => '',
                'help' => '',
                'importable' => 'true',
                'duplicate_merge' => 'disabled',
                'duplicate_merge_dom_value' => '0',
                'audited' => false,
                'reportable' => true,
                'unified_search' => false,
                'merge_filter' => 'disabled',
                'calculated' => false,
                'size' => '20',
                'enable_range_search' => false,
                'dbType' => 'datetime',
            ),
        'rst_update_date' =>
            array (
                'required' => false,
                'name' => 'rst_update_date',
                'vname' => 'LBL_RST_UPDATE_DATE',
                'type' => 'datetimecombo',
                'massupdate' => true,
                'no_default' => false,
                'comments' => '',
                'help' => '',
                'importable' => 'true',
                'duplicate_merge' => 'disabled',
                'duplicate_merge_dom_value' => '0',
                'audited' => false,
                'reportable' => true,
                'unified_search' => false,
                'merge_filter' => 'disabled',
                'calculated' => false,
                'size' => '20',
                'enable_range_search' => false,
                'dbType' => 'datetime',
            ),
    ),
    'relationships'=>array (
    ),
    'optimistic_locking'=>true,
    'unified_search'=>true,
    'acls' => array(
        'SugarACLDeveloperForTarget' => array(
            'targetModuleField' => 'rst_module', 'allowUserRead' => false
        )
    ),
    'visibility' => array(
        'TargetModuleDeveloperVisibility' => array('targetModuleField' => 'rst_module')
    ),
    'hidden_to_role_assignment' => true,
    // @TODO Fix the Default and Basic SugarObject templates so that Basic
    // implements Default. This would allow the application of various
    // implementations on Basic without forcing Default to have those so that
    // situations like this - implementing taggable - doesn't have to apply to
    // EVERYTHING. Since there is no distinction between basic and default for
    // sugar objects templates yet, we need to forecefully remove the taggable
    // implementation fields. Once there is a separation of default and basic
    // templates we can safely remove these as this module will implement
    // default instead of basic.
    'ignore_templates' => array(
        'taggable',
    ),
);

if (!class_exists('VardefManager')) {
    require_once('include/SugarObjects/VardefManager.php');
}

VardefManager::createVardef('pmse_Business_Rules', 'pmse_Business_Rules', array('basic', 'team_security', 'assignable'));
