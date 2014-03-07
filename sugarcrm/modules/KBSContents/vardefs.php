<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
 * Copyright  2004-2014 SugarCRM Inc.  All rights reserved.
 */

$dictionary['KBSContent'] = array(
    'table' => 'kbscontents',
    'audited' => true,
    'comment' => 'A content represents information about document',
    'fields' => array(
        'kbdocument_body' => array(
            'name' => 'kbdocument_body',
            'vname' => 'LBL_TEXT_BODY',
            'type' => 'longtext',
            'comment' => 'Article body',
        ),
        'language' => array(
            'name' => 'language',
            'type' => 'varchar',
            'len' => '2',
            'required' => true,
            'vname' => 'LBL_LANG',
        ),
        'active_date' => array(
            'name' => 'active_date',
            'vname' => 'LBL_ACTIVE_DATE',
            'type' => 'date',
            'importable' => 'required',
            'sortable' => true,
        ),
        'exp_date' => array(
            'name' => 'exp_date',
            'vname' => 'LBL_EXP_DATE',
            'type' => 'date',
            'sortable' => true,
        ),
        'doc_id' => array(
            'name' => 'doc_id',
            'vname' => 'LBL_DOC_ID',
            'type' => 'id',
            'sortable' => false,
            'required' => true,
        ),
        'approved' => array(
            'name' => 'approved',
            'vname' => 'LBL_APPROVED',
            'type' => 'bool',
            'sortable' => true,
        ),
        'status' => array(
            'name' => 'status',
            'vname' => 'LBL_STATUS',
            'type' => 'enum',
            'len' => 100,
            'options' => 'kbdocument_status_dom',
            'reportable' => false,
        ),
        'viewcount' => array(
            'name' => 'viewcount',
            'vname' => 'LBL_VIEWED_COUNT',
            'len' => '26,6',
            'type' => 'decimal',
            'importable' => 'required',
            'default' => 0,
            'sortable' => true,
        ),
        'revision' => array(
            'name' => 'revision',
            'vname' => 'LBL_REVISION',
            'type' => 'varchar',
            'len' => '10',
            'required' => true,
        ),
        'rating' => array(
            'name' => 'rating',
            'vname' => 'LBL_RATING',
            'source' => 'non-db',
        ),
        'useful' => array(
            'name' => 'useful',
            'vname' => 'LBL_USEFUL',
            'type' => 'int',
            'default' => '0',
        ),
        'notuseful' => array(
            'name' => 'notuseful',
            'vname' => 'LBL_NOT_USEFUL',
            'type' => 'int',
            'default' => '0',
        ),
    ),
);

VardefManager::createVardef('KBSContents','KBSContent', array('basic', 'team_security'));
