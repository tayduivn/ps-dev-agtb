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
 * Copyright  2004-2014 SugarCRM Inc.  All rights reserved.
 */

$dictionary['KBSTopic'] = array(
    'table' => 'kbstopics',
    'comment' => 'Knowledge Base Topicss',
    'audited' => true,
    'activity_enabled'=>false,
    'unified_search' => false,
    'unified_search_default_enabled' => false,
    'full_text_search' => false,
    'fields' => array(
        'name' => array(
            'name' => 'name',
            'vname' => 'LBL_NAME',
            'type' => 'name',
            'dbType' => 'varchar',
            'len' => '255',
            'comment' => 'Topic name',
            'required' => true,
        ),
        'parent_id' => array(
            'name' => 'parent_id',
            'vname' => 'LBL_PARENT_NAME',
            'type' => 'id',
            'isnull' => 'true',
            'comment' => 'Parent topic',
        ),
        'parent_name' => array(
            'name' => 'parent_name',
            'rname' => 'name',
            'id_name' => 'parent_id',
            'vname' => 'LBL_PARENT_NAME',
            'type' => 'relate',
            'isnull' => 'true',
            'module' => 'KBSTopics',
            'table' => 'kbstopics',
            'massupdate' => false,
            'source' => 'non-db',
            'link' => 'parent_topic',
        ),
        'parent_topic' => array(
            'name' => 'topics',
            'type' => 'link',
            'relationship' => 'kbstopics_relations',
            'module' => 'KBSTopics',
            'bean_name' => 'KBSTopic',
            'source' => 'non-db',
            'vname' => 'LNK_TOPICS',
            'side' => 'right',
        ),
        'subnodes' => array(
            'name' => 'subnodes',
            'type' => 'link',
            'relationship' => 'kbstopics_relations',
            'module' => 'KBSTopics',
            'bean_name' => 'KBSTopic',
            'source' => 'non-db',
            'vname' => 'LNK_TOPICS',
        ),
    ),
    'acls' => array(
        'SugarACLAdminOnly' => array(
            'adminFor' => 'Users',
        ),
    ),
    'indices' => array(
        array('name' => 'idx_kbstopics_parent_id', 'type' => 'index', 'fields' => array('parent_id')),
    ),
    'relationships' => array(
        'kbstopics_relations' => array(
            'lhs_module' => 'KBSTopics',
            'lhs_table' => 'kbstopics',
            'lhs_key' => 'id',
            'rhs_module' => 'KBSTopics',
            'rhs_table' => 'kbstopics',
            'rhs_key' => 'parent_id',
            'relationship_type' => 'one-to-many'
        )
    )
);

VardefManager::createVardef('KBSTopics', 'KBSTopic', array('basic'));
