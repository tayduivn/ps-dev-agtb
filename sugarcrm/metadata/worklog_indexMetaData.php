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

$dictionary['worklog_index'] = array(
    'table' => 'worklog_index',
    'relationships' => array(),
    'fields' => array(
        'id' => array(
            'name' => 'id',
            'type' => 'id',
            'required' => true,
        ),
        'record_id' => array(
            'name' => 'record_id',
            'type' => 'id',
            'required' => true,
        ),
        'worklog_id' => array(
            'name' => 'worklog_id',
            'type' => 'id',
            'required' => true,
        ),
        'module' => array(
            'name' => 'module',
            'type' => 'varchar',
            'len' => 100,
            'required' => false,
            'readonly' => true,
        ),
        'deleted' => array(
            'name' => 'deleted',
            'type' => 'bool',
            'default' => '0',
        ),
    ),
    'indices' => array(
        array(
            'name' => 'worklog_relpk',
            'type' => 'primary',
            'fields' => array('id'),
        ),
        array(
            'name' => 'worklog_record_relpk',
            'type' => 'index',
            'fields' => array('record_id'),
        ),
        array(
            'name' => 'worklog_worklog_relpk',
            'type' => 'index',
            'fields' => array('worklog_id'),
        ),
    ),
);
