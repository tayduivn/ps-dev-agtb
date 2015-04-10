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
$adminRoute = '#bwc/index.php?module=Administration&action=';
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
    array(
        'label' => 'LBL_MANAGE_PASSWORD_TITLE',
        'acl_action' => 'studio',
        'module' => $moduleName,
        'icon' => 'fa-cogs',
        'route' => $adminRoute . 'PasswordManager',
    ),

    // Sugar Connect
    array(
        'label' => 'LBL_MANAGE_LICENSE_TITLE',
        'acl_action' => 'studio',
        'module' => $moduleName,
        'icon' => 'fa-cogs',
        'route' => $adminRoute . 'LicenseSettings',
    ),
    array(
        'label' => 'LBL_SUGAR_UPDATE_TITLE',
        'acl_action' => 'studio',
        'module' => $moduleName,
        'icon' => 'fa-cogs',
        'route' => $adminRoute . 'Updater',
    ),

    // System
    array(
        'label' => 'LBL_CONFIGURE_SETTINGS_TITLE',
        'acl_action' => 'studio',
        'module' => $moduleName,
        'icon' => 'fa-cogs',
        'route' => '#bwc/index.php?module=Configurator&action=EditView',
    ),
    array(
        'label' => 'LBL_IMPORT_WIZARD',
        'acl_action' => 'studio',
        'module' => $moduleName,
        'icon' => 'fa-cogs',
        'route' => '#bwc/index.php?module=Import&action=step1&import_module=Administration',
    ),

    array(
        'label' => 'LBL_MANAGE_LOCALE',
        'acl_action' => 'studio',
        'module' => $moduleName,
        'icon' => 'fa-cogs',
        'route' => $adminRoute . 'Locale&view=default',
    ),
    array(
        'label' => 'LBL_UPGRADE_WIZARD_TITLE',
        'acl_action' => 'studio',
        'module' => $moduleName,
        'icon' => 'fa-cogs',
        'route' => 'UpgradeWizard.php',
    ),

    array(
        'label' => 'LBL_MANAGE_CURRENCIES',
        'acl_action' => 'studio',
        'module' => $moduleName,
        'icon' => 'fa-cogs',
        'route' => '#bwc/index.php?module=Currencies&action=index',
    ),
    array(
        'label' => 'LBL_BACKUPS_TITLE',
        'acl_action' => 'studio',
        'module' => $moduleName,
        'icon' => 'fa-cogs',
        'route' => $adminRoute . 'Backups',
    ),

    array(
        'label' => 'LBL_MANAGE_LANGUAGES',
        'acl_action' => 'studio',
        'module' => $moduleName,
        'icon' => 'fa-cogs',
        'route' => $adminRoute . 'Languages&view=default',
    ),
    array(
        'label' => 'LBL_UPGRADE_TITLE',
        'acl_action' => 'studio',
        'module' => $moduleName,
        'icon' => 'fa-cogs',
        'route' => $adminRoute . 'Upgrade',
    ),
    array(
        'label' => 'LBL_QUICK_REPAIR_AND_REBUILD',
        'acl_action' => 'studio',
        'module' => $moduleName,
        'icon' => 'fa-cogs',
        'route' => $adminRoute . 'repair',
    ),

    array(
        'label' => 'LBL_GLOBAL_SEARCH_SETTINGS',
        'acl_action' => 'studio',
        'module' => $moduleName,
        'icon' => 'fa-cogs',
        'route' => $adminRoute . 'GlobalSearchSettings',
    ),
    array(
        'label' => 'LBL_DIAGNOSTIC_TITLE',
        'acl_action' => 'studio',
        'module' => $moduleName,
        'icon' => 'fa-cogs',
        'route' => $adminRoute . 'Diagnostic',
    ),


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
