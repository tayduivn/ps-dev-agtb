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
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

$viewdefs['base']['view']['profileactions'] = array(
    array(
        'route' => '#bwc/index.php?module=Users&action=DetailView&record=',
        'label' => 'LBL_PROFILE',
        'css_class' => 'profileactions-profile',
        'acl_action' => 'view',
        'icon' => 'icon-user',
        'submenu' => '',
    ),
    array(
        'route'=> '#bwc/index.php?module=Employees&action=index&query=true',
        'label' => 'LBL_EMPLOYEES',
        'css_class' => 'profileactions-employees',
        'acl_action' => 'list',
        'icon' => 'icon-group',
        'submenu' => '',
    ),
    array(
        'route' => '#bwc/index.php?module=Administration&action=index',
        'label' => 'LBL_ADMIN',
        'css_class' => 'administration',
        'acl_action' => 'admin',
        'icon' => 'icon-cogs',
        'submenu' => '',
    ),
    array(
        'route' => '#about',
        'label' => 'LNK_ABOUT',
        'css_class' => 'profileactions-about',
        'acl_action' => 'view',
        'icon' => 'icon-info-sign',
        'submenu' => '',
    ),
    array(
        'route' => '#logout/?clear=1',
        'label' => 'LBL_LOGOUT',
        'css_class' => 'profileactions-logout',
        'icon' => 'icon-signout',
        'submenu' => '',
    ),
);
