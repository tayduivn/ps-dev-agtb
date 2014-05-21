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
    'activity_enabled' => true,
    'fields' => array(
        'contents' => array(
            'name' => 'contents',
            'type' => 'link',
            'relationship' => 'kbstopic_contents',
            'module' => 'KBSContents',
            'bean_name' => 'KBSContent',
            'source' => 'non-db',
            'vname' => 'LNK_CONTENTS',
        ),
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
            'isnull' => true,
            'module' => 'KBSTopics',
            'table' => 'kbstopics',
            'massupdate' => false,
            'source' => 'non-db',
            'link' => 'parent',
        ),
        'parent' => array(
            'name' => 'parent',
            'type' => 'link',
            'relationship' => 'kbstopics_subnodes',
            'module' => 'KBSTopics',
            'bean_name' => 'KBSTopic',
            'source' => 'non-db',
            'vname' => 'LNK_TOPICS',
            'side' => 'right',
        ),
        'subnodes' => array(
            'name' => 'subnodes',
            'type' => 'link',
            'relationship' => 'kbstopics_subnodes',
            'module' => 'KBSTopics',
            'bean_name' => 'KBSTopic',
            'source' => 'non-db',
            'vname' => 'LNK_TOPICS',
        ),
    ),
    'relationships' => array(
        'kbstopic_contents' => array(
            'lhs_module' => 'KBSTopics',
            'lhs_table' => 'kbstopics',
            'lhs_key' => 'id',
            'rhs_module' => 'KBSContents',
            'rhs_table' => 'kbscontents',
            'rhs_key' => 'topic_id',
            'relationship_type' => 'one-to-many',
        ),
        'kbstopics_subnodes' => array(
            'lhs_module' => 'KBSTopics',
            'lhs_table' => 'kbstopics',
            'lhs_key' => 'id',
            'rhs_module' => 'KBSTopics',
            'rhs_table' => 'kbstopics',
            'rhs_key' => 'parent_id',
            'relationship_type' => 'one-to-many',
        ),
    ),
    'duplicate_check' => array(
        'enabled' => false,
    ),
);

VardefManager::createVardef('KBSTopics', 'KBSTopic', array('basic', 'team_security'));
