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

$dictionary['commentslog_rel'] = array(
    'table' => 'commentslog_rel',
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
        'commentslog_id' => array(
            'name' => 'commentslog_id',
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
            'name' => 'commentslog_relpk',
            'type' => 'primary',
            'fields' => array('id'),
        ),
        array(
            'name' => 'commentslog_record_relpk',
            'type' => 'index',
            'fields' => array('record_id'),
        ),
        array(
            'name' => 'commentslog_commentslog_relpk',
            'type' => 'index',
            'fields' => array('commentslog_id'),
        ),
    ),
);
