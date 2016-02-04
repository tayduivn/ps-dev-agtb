<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
$dictionary['calls_addressees'] = array(
    'table' => 'calls_addressees',
    'fields' => array(
        array('name' => 'id', 'type' => 'id', 'len' => '36'),
        array('name' => 'call_id', 'type' => 'id', 'len' => '36'),
        array('name' => 'addressee_id', 'type' => 'id', 'len' => '36'),
        array('name' => 'required', 'type' => 'varchar', 'len' => '1', 'default' => '1'),
        array('name' => 'accept_status', 'type' => 'varchar', 'len' => '25', 'default' => 'none'),
        array('name' => 'date_modified', 'type' => 'datetime'),
        array('name' => 'deleted', 'type' => 'bool', 'len' => '1', 'default' => '0', 'required' => false),
    ),
    'indices' => array(
        array('name' => 'calls_addressees_pk', 'type' => 'primary', 'fields' => array('id')),
        array('name' => 'idx_addressee_call_call', 'type' => 'index', 'fields' => array('call_id')),
        array('name' => 'idx_addressee_call_addressee', 'type' => 'index', 'fields' => array('addressee_id')),
        array('name' => 'idx_call_addressee', 'type' => 'alternate_key', 'fields' => array('call_id', 'addressee_id')),
    ),
    'relationships' => array(
        'calls_addressees' => array(
            'lhs_module' => 'Calls',
            'lhs_table' => 'calls',
            'lhs_key' => 'id',
            'rhs_module' => 'Addressees',
            'rhs_table' => 'addressees',
            'rhs_key' => 'id',
            'relationship_type' => 'many-to-many',
            'join_table' => 'calls_addressees',
            'join_key_lhs' => 'call_id',
            'join_key_rhs' => 'addressee_id',
        ),
    ),
);
