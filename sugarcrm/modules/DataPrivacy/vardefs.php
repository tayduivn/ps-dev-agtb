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

$dictionary['DataPrivacy'] = array(
    'table' => 'data_privacy',
    'audited' => false,
    'activity_enabled' => true,
    'unified_search' => true,
    'full_text_search' => true,
    'unified_search_default_enabled' => true,
    'duplicate_merge'=>false,
    'comment' => 'Requests regarding the data we have collected on our customers',
    'fields' => array(
        'type' => array (
            'name' => 'type',
            'vname' => 'LBL_TYPE',
            'type' => 'enum',
            'options' => 'dataprivacy_type_dom',
            'len'=>255,
            'unified_search' => true,
            'full_text_search' => array(
                'enabled' => true,
                'searchable' => true,
                'boost' => 1.25,
            ),
            'comment' => 'The type of request',
            'sortable' => true,
            'duplicate_on_record_copy' => 'always',
            'required' => true,
        ),
        'source' =>
            array (
                'name' => 'source',
                'vname' => 'LBL_SOURCE',
                'type' => 'text',
                'required' => true,
                'full_text_search' => array('enabled' => true, 'searchable' => true, 'boost' => 0.65),
                'comment' => 'The source of the request',
            ),
        'requested_by' =>
            array(
                'name' => 'requested_by',
                'vname' => 'LBL_REQUESTED_BY',
                'type' => 'text',
                'comment' => 'Requested by',
            ),
        'date_due' =>
            array(
                'name' => 'date_due',
                'vname' => 'LBL_DATE_DUE',
                'type' => 'date',
                'options' => 'date_range_search_dom',
                'enable_range_search' => true,
                'full_text_search' => array(
                    'enabled' => true,
                    'searchable' => false,
                    'sortable' => true,
                 ),
                 'comment' => 'Due date',
             ),
        'resolution' =>
            array (
                'name' => 'resolution',
                'vname' => 'LBL_RESOLUTION',
                'type' => 'text',
                'full_text_search' => array('enabled' => true, 'searchable' => true, 'boost' => 0.65),
                'comment' => 'The resolution of the request',
            ),
        'date_resolved' =>
            array(
                'name' => 'date_resolved',
                'vname' => 'LBL_DATE_RESOLVED',
                'type' => 'date',
                'options' => 'date_range_search_dom',
                'enable_range_search' => true,
                'full_text_search' => array(
                    'enabled' => true,
                    'searchable' => false,
                    'sortable' => true,
                ),
                'comment' => 'Date resolved',
            ),
        'fields_to_erase' => array(
            'name' => 'fields_to_erase',
            'type' => 'json',
            'dbType' => 'text',
            'studio' => false,
        ),
        'leads' =>
            array (
                'name' => 'leads',
                'type' => 'link',
                'relationship' => 'leads_dataprivacy',
                'source'=>'non-db',
                'vname'=>'LBL_LEADS',
            ),
        'contacts' =>
            array (
                'name' => 'contacts',
                'type' => 'link',
                'relationship' => 'contacts_dataprivacy',
                'source'=>'non-db',
                'vname'=>'LBL_CONTACTS',
            ),
        'prospects' =>
            array (
                'name' => 'prospects',
                'type' => 'link',
                'relationship' => 'prospects_dataprivacy',
                'source'=>'non-db',
                'vname'=>'LBL_PROSPECTS',
            ),
    ),
    'indices' => array(
        array('name' =>'dataprivacy_number' , 'type'=>'unique' , 'fields'=>array('dataprivacy_number', 'system_id')),
        array('name' =>'idx_dataprivacy_name', 'type' =>'index', 'fields'=>array('name')),
    ),
    'acls' => array('SugarACLStatic' => true),
);

VardefManager::createVardef('DataPrivacy', 'DataPrivacy', array('default', 'assignable', 'team_security', 'issue'));

// boost value for full text search. copied from Case.
$dictionary['DataPrivacy']['fields']['name']['full_text_search']['boost'] = 1.53;
$dictionary['DataPrivacy']['fields']['DataPrivacy_number']['full_text_search']['boost'] = 1.29;
$dictionary['DataPrivacy']['fields']['description']['full_text_search']['boost'] = 0.66;
