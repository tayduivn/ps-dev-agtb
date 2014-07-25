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

$module_name = 'KBSContents';
$viewdefs[$module_name]['base']['menu']['header'] = array(
    array(
        'route' => "#{$module_name}/create",
        'label' => 'LNK_NEW_ARTICLE',
        'acl_action' => 'create',
        'acl_module' => $module_name,
        'icon' => 'icon-plus',
    ),
    array(
        'route' => "#KBSTopics/create",
        'label' => 'LNK_NEW_TOPIC',
        'acl_action' => 'admin',
        'acl_module' => 'KBSTopics',
        'icon' => 'icon-plus',
    ),
    array(
        'route' => "#KBSContentTemplates/create",
        'label' => 'LNK_NEW_KBSCONTENT_TEMPLATE',
        'acl_action' => 'admin',
        'acl_module' => 'KBSContentTemplates',
        'icon' => 'icon-plus',
    ),
    array(
        'route' => "#{$module_name}",
        'label' => 'LBL_LIST_ARTICLES',
        'acl_action' => 'list',
        'acl_module' => $module_name,
        'icon' => 'icon-reorder',
    ),
    array(
        'route' => "#KBSTopics",
        'label' => 'LNK_TOPIC_LIST',
        'acl_action' => 'admin',
        'acl_module' => 'KBSTopics',
        'icon' => 'icon-reorder',
    ),
    array(
        'route' => "#KBSContentTemplates",
        'label' => 'LNK_LIST_KBSCONTENT_TEMPLATES',
        'acl_action' => 'list',
        'acl_module' => 'KBSContentTemplates',
        'icon' => 'icon-reorder',
    ),
    array(
        'route' => "#{$module_name}/config",
        'label' => 'LBL_KNOWLEDGE_BASE_ADMIN_MENU',
        'acl_action' => 'admin',
        'acl_module' => $module_name,
        'icon' => 'icon-question-sign',
    ),
);

