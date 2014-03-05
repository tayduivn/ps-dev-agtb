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
            'type' => 'int',
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
        'attachment_list' => array(
            'name' => 'attachment_list',
            'type' => 'file',
            'source' => 'non-db',
            'vname' => 'LBL_RATING',
        ),
        'notes' => array(
            'name' => 'notes',
            'vname' => 'LBL_ATTACHMENTS',
            'type' => 'link',
            'relationship' => 'kbscontent_notes',
            'module' => 'Notes',
            'bean_name' => 'Note',
            'source' => 'non-db',
        ),
        'topic_id' => array(
            'name' => 'topic_id',
            'vname' => 'LBL_TOPIC_ID',
            'type' => 'id',
            'isnull' => 'true',
            'comment' => 'Topic ID',
        ),
        'topic_name' => array(
            'name' => 'topic_name',
            'rname' => 'name',
            'id_name' => 'topic_id',
            'vname' => 'LBL_TOPIC_NAME',
            'type' => 'relate',
            'isnull' => 'true',
            'module' => 'KBSTopics',
            'table' => 'kbstopics',
            'massupdate' => false,
            'source' => 'non-db',
            'link' => 'parent_topic',
        ),
        'topic' => array(
            'name' => 'topic',
            'type' => 'link',
            'relationship' => 'kbscontent_topic',
            'module' => 'KBSTopics',
            'bean_name' => 'KBSTopic',
            'source' => 'non-db',
            'vname' => 'LNK_TOPICS',
            'side' => 'right',
        ),
        'kbsdocuments_kbscontents' => array(
            'name' => 'kbsdocuments_kbscontents',
            'type' => 'link',
            'vname' => 'LBL_KBSDOCUMENTS',
            'relationship' => 'kbsdocuments_kbscontents',
            'source' => 'non-db',
        ),
        'kbsdocument_id' => array(
            'name' => 'kbsdocument_id',
            'id_name' => 'kbsdocument_id',
            'vname' => 'LBL_KBSDOCUMENT_ID',
            'rname' => 'id',
            'type' => 'id',
            'table' => 'kbsdocuments',
            'isnull' => 'true',
            'module' => 'KBSDocuments',
            'reportable' => false,
            'massupdate' => false,
            'duplicate_merge' => 'disabled',
        ),
        'kbsdocument_name' => array(
            'name' => 'kbsdocument_name',
            'rname' => 'name',
            'vname' => 'LBL_KBSDOCUMENT',
            'type' => 'relate',
            'reportable' => false,
            'source' => 'non-db',
            'table' => 'kbsdocuments',
            'id_name' => 'kbsdocument_id',
            'link' => 'kbsdocuments_kbscontents',
            'module' => 'KBSDocuments',
            'duplicate_merge' => 'disabled',
        ),       
    ),
    'relationships' => array(
        'kbscontent_notes' => array(
            'lhs_module' => 'KBSContents',
            'lhs_table' => 'kbscontents',
            'lhs_key' => 'id',
            'rhs_module' => 'Notes',
            'rhs_table' => 'notes',
            'rhs_key' => 'parent_id',
            'relationship_type' => 'one-to-many',
        ),
        'kbscontent_topic' => array(
            'lhs_module' => 'KBSContents',
            'lhs_table' => 'kbscontents',
            'lhs_key' => 'id',
            'rhs_module' => 'KBSTopics',
            'rhs_table' => 'kbstopics',
            'rhs_key' => 'topic_id',
            'relationship_type' => 'one-to-many'
        )
        'kbsdocuments_kbscontents' => array (
            'lhs_module' => 'KBSDocuments',
            'lhs_table' => 'kbsdocuments',
            'lhs_key' => 'id',
            'rhs_module' => 'KBSContents',
            'rhs_table' => 'kbscontents',
            'rhs_key' => 'kbsdocument_id',
            'relationship_type' => 'one-to-many'
        ),

    ),

    'duplicate_check' => array(
        'enabled' => false,
    ),
);

VardefManager::createVardef('KBSContents','KBSContent', array('basic', 'team_security'));
