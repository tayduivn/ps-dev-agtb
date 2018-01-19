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

$dictionary['ReportSchedule'] = array(
    'table' => 'report_schedules',
    'fields' => array(
        'user_id' => array(
            'name' => 'user_id',
            'type' => 'id',
        ),
        'report_id' => array(
            'name' => 'report_id',
            'type' => 'id',
            'required' => true,
        ),
        'report_name' => array(
            'name' => 'report_name',
            'rname' => 'name',
            'id_name' => 'report_id',
            'vname' => 'LBL_REPORT_NAME',
            'type' => 'relate',
            'table' => 'saved_reports',
            'isnull' => false,
            'module' => 'Reports',
            'dbType' => 'varchar',
            'link' => 'report',
            'len' => '255',
            'source'=>'non-db',
        ),
        'report'=> array (
            'name' => 'report',
            'type' => 'link',
            'relationship' => 'report_reportschedules',
            'source' => 'non-db',
            'vname'=> 'LBL_REPORTS',
        ),
        'date_start' => array(
            'name' => 'date_start',
            'vname' => 'LBL_DATE_START',
            'type' => 'datetime',
            'required' => true,
        ),
        'time_interval' => array(
            'name' => 'time_interval',
            'type' => 'enum',
            'dbType' => 'int',
            'len' => 11,
            'vname' => 'LBL_TIME_INTERVAL',
            'options' => 'reportschedule_time_interval_dom',
            'required' => true,
        ),
        'next_run' => array(
            'name' => 'next_run',
            'type' => 'datetime',
            'vname' => 'LBL_NEXT_RUN',
        ),
        'active' => array(
            'name' => 'active',
            'vname' => 'LBL_ACTIVE',
            'type' => 'bool',
            'len' => '1',
            'default' => '0',
            'required' => true,
        ),
        'schedule_type' => array(
            'name' => 'schedule_type',
            'type' => 'varchar',
            'len' => 3,
            'vname' => 'LBL_SCHEDULE_TYPE',
            'required' => true,
            'comment' => 'Legacy field. ent for advanced reports, pro for regular reports',
        ),
        'users' => array(
            'name' => 'users',
            'type' => 'link',
            'relationship' => 'reportschedules_users',
            'source' => 'non-db',
            'vname' => 'LBL_USERS',
        ),
    ),
    'relationships' => array(
        'report_reportschedules' => array (
            'lhs_module' => 'Reports',
            'lhs_table' => 'saved_reports',
            'lhs_key' => 'id',
            'rhs_module' => 'ReportSchedules',
            'rhs_table' => 'report_schedules',
            'rhs_key' => 'report_id',
            'relationship_type' => 'one-to-many',
        ),
    ),
);

VardefManager::createVardef(
    'ReportSchedules',
    'ReportSchedule',
    array(
        'basic',
        'assignable',
        'team_security',
    )
);
