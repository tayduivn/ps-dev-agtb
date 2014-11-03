<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
        'route' => "#KBSContentTemplates",
        'label' => 'LNK_LIST_KBSCONTENT_TEMPLATES',
        'acl_action' => 'list',
        'acl_module' => 'KBSContentTemplates',
        'icon' => 'icon-reorder',
    ),
    array(
        'event' => 'tree:list:fire',
        'label' => 'LNK_LIST_KBSTOPICS',
        'acl_action' => 'list',
        'acl_module' => $module_name,
        'icon' => 'icon-reorder',
        'target' => 'view',
    ),
    array(
        'route' => '#bwc/index.php?' . http_build_query(
                array(
                    'module' => 'Import',
                    'action' => 'Step1',
                    'import_module' => $module_name,
                )
            ),
        'label' => 'LNK_IMPORT_KBSCONTENTS',
        'acl_action' => 'import',
        'acl_module' => $module_name,
        'icon' => 'icon-upload',
    ),
    array(
        'route' => "#{$module_name}/config",
        'label' => 'LBL_KNOWLEDGE_BASE_ADMIN_MENU',
        'acl_action' => 'admin',
        'acl_module' => $module_name,
        'icon' => 'icon-question-sign',
    ),
);

