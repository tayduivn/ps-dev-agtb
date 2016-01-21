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
$dictionary['meetings_addresses'] = array(
    'table' => 'meetings_addresses',
    'fields' => array(
        array('name' => 'id', 'type' => 'id', 'len' => '36'),
        array('name' => 'meeting_id', 'type' => 'id', 'len' => '36'),
        array('name' => 'addressee_id', 'type' => 'id', 'len' => '36'),
        array('name' => 'required', 'type' => 'varchar', 'len' => '1', 'default' => '1'),
        array('name' => 'accept_status', 'type' => 'varchar', 'len' => '25', 'default' => 'none'),
        array('name' => 'date_modified', 'type' => 'datetime'),
        array('name' => 'deleted', 'type' => 'bool', 'len' => '1', 'default' => '0', 'required' => false),
    ),
    'indices' => array(
        array('name' => 'meetings_addressespk', 'type' => 'primary', 'fields' => array('id')),
        array('name' => 'idx_addressee_meeting_meeting', 'type' => 'index', 'fields' => array('meeting_id')),
        array('name' => 'idx_addressee_meeting_addressee', 'type' => 'index', 'fields' => array('addressee_id')),
        array('name' => 'idx_meeting_addressee', 'type' => 'alternate_key', 'fields' => array('meeting_id', 'addressee_id')),
    ),
    'relationships' => array(
        'meetings_addresses' => array(
            'lhs_module' => 'Meetings',
            'lhs_table' => 'meetings',
            'lhs_key' => 'id',
            'rhs_module' => 'Addresses',
            'rhs_table' => 'addresses',
            'rhs_key' => 'id',
            'relationship_type' => 'many-to-many',
            'join_table' => 'meetings_addresses',
            'join_key_lhs' => 'meeting_id',
            'join_key_rhs' => 'addressee_id',
        ),
    ),
);
