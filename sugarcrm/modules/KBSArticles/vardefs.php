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
$dictionary['KBSArticle'] = array(
    'table' => 'kbsarticles',
    'favorites' => true,
    'unified_search' => true,
    'full_text_search' => true,
    'comment' => 'Knowledge Base Article',
    'fields' => array(
        'kbsdocuments_kbsarticles' => array(
            'name' => 'kbsdocuments_kbsarticles',
            'type' => 'link',
            'vname' => 'LBL_KBSDOCUMENTS',
            'relationship' => 'kbsdocuments_kbsarticles',
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
            'link' => 'kbsdocuments_kbsarticles',
            'module' => 'KBSDocuments',
            'duplicate_merge' => 'disabled',
        ),
        'kbsarticles_kbscontents' => array(
            'name' => 'kbsarticles_kbscontents',
            'type' => 'link',
            'vname' => 'LBL_KBSARTICLES',
            'relationship' => 'kbsarticles_kbscontents',
            'source' => 'non-db',
            'side' => 'right',
        ),
    ),
    'relationships' => array(
        'kbsdocuments_kbsarticles' => array (
            'lhs_module' => 'KBSDocuments',
            'lhs_table' => 'kbsdocuments',
            'lhs_key' => 'id',
            'rhs_module' => 'KBSArticles',
            'rhs_table' => 'kbsarticles',
            'rhs_key' => 'kbsdocument_id',
            'relationship_type' => 'one-to-many',
        ),
        'kbsarticles_kbscontents' => array (
            'lhs_module' => 'KBSArticles',
            'lhs_table' => 'kbsarticles',
            'lhs_key' => 'id',
            'rhs_module' => 'KBSContents',
            'rhs_table' => 'kbscontents',
            'rhs_key' => 'kbsarticle_id',
            'relationship_type' => 'one-to-many',
        ),
    ),
);

VardefManager::createVardef(
    'KBSArticles',
    'KBSArticle',
    array(
        'basic',
        'team_security',
        'assignable',
    )
);
