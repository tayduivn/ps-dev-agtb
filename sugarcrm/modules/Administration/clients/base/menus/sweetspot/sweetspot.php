<?php
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
$moduleName = 'Administration';
$viewdefs[$moduleName]['base']['menu']['sweetspot'] = array(
    // Users and security
    array(
        'label' => 'LBL_MANAGE_USERS_TITLE',
        'acl_action' => 'studio',
        'module' => $moduleName,
        'icon' => 'fa-cogs',
        'route' => '#bwc/index.php?module=Users&action=index',
    ),
    array(
        'label' => 'LBL_MANAGE_ROLES_TITLE',
        'acl_action' => 'studio',
        'module' => $moduleName,
        'icon' => 'fa-cogs',
        'route' => '#bwc/index.php?module=ACLRoles&action=index',
    ),
    array(
        'label' => 'LBL_MANAGE_TEAMS_TITLE',
        'acl_action' => 'studio',
        'module' => $moduleName,
        'icon' => 'fa-cogs',
        'route' => '#bwc/index.php?module=Teams&action=index',
    ),
//    array(
//        'label' => 'LBL_MANAGE_PASSWORD_TITLE',
//        'acl_action' => 'studio',
//        'module' => $moduleName,
//        'icon' => 'fa-cogs',
//        'route' => '#bwc/index.php?module=Administration&action=PasswordManager',
//    ),

    // System
    array(
        'label' => 'LBL_CONFIGURE_SETTINGS_TITLE',
        'acl_action' => 'studio',
        'module' => $moduleName,
        'icon' => 'fa-cogs',
        'route' => '#bwc/index.php?module=Configurator&action=EditView',
    ),
//    array(
//        'label' => 'LBL_UPGRADE_TITLE',
//        'acl_action' => 'studio',
//        'module' => $moduleName,
//        'icon' => 'fa-cogs',
//        'route' => '#bwc/index.php?module=Administration&action=Upgrade',
//    ),
//    array(
//        'label' => 'LBL_GLOBAL_SEARCH_SETTINGS',
//        'acl_action' => 'studio',
//        'module' => $moduleName,
//        'icon' => 'fa-cogs',
//        'route' => '#bwc/index.php?module=Administration&action=GlobalSearchSettings',
//    ),

    // Developer Tools
    array(
        'label' => 'LBL_STUDIO',
        'acl_action' => 'studio',
        'module' => $moduleName,
        'icon' => 'fa-cogs',
        'route' => '#bwc/index.php?module=ModuleBuilder&action=index&type=studio',
    ),
    array(
        'label' => 'LBL_MANAGE_STYLEGUIDE',
        'acl_action' => 'studio',
        'module' => $moduleName,
        'icon' => 'fa-cogs',
        'route' => '#Styleguide',
    ),
);
