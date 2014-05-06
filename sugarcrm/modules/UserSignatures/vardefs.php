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
$dictionary['UserSignature'] = array(
    'table' => 'users_signatures',
    'fields' => array(
        'user_id' => array(
            'name' => 'user_id',
            'vname' => 'LBL_USER_ID',
            'type' => 'id',
            'len' => 36,
        ),
        'name' => array(
            'name' => 'name',
            'vname' => 'LBL_NAME',
            'type' => 'name',
            'dbType' => 'varchar',
            'len' => 255,
            'unified_search' => true,
            'full_text_search' => array('enabled' => true, 'boost' => 3),
            'required' => true,
            'importable' => 'required',
            'duplicate_merge' => 'enabled',
            'merge_filter' => 'selected',
            'duplicate_on_record_copy' => 'always',
        ),
        'signature' => array(
            'name' => 'signature',
            'vname' => 'LBL_SIGNATURE',
            'type' => 'text',
            'reportable' => false,
        ),
        'signature_html' => array(
            'name' => 'signature_html',
            'vname' => 'LBL_SIGNATURE_HTML',
            'type' => 'text',
            'reportable' => false,
        ),
    ),
    'indices' => array(
        array(
            'name' => 'idx_usersig_uid',
            'type' => 'index',
            'fields' => array('user_id'),
        ),
        array(
            'name' => 'idx_usersig_created_by',
            'type' => 'index',
            'fields' => array('created_by'),
        ),
    ),
);
VardefManager::createVardef('UserSignatures', 'UserSignature', array('default'));
