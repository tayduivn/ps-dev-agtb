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
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

$viewdefs['KBSContents']['base']['view']['list'] = array(
    'panels' => array(
        array(
            'label' => 'LBL_PANEL_1',
            'fields' => array(
                array(
                    'name' => 'name',
                    'label' => 'LBL_NAME',
                    'default' => true,
                    'enabled' => true,
                    'link' => true,
                    'related_fields' => array(
                        'kbsdocument_id',
                        'kbsarticle_id',
                    ),
                ),
                array(
                    'name' => 'language',
                    'label' => 'LBL_LANG',
                    'default' => true,
                    'enabled' => true,
                    'link' => true,
                    'type' => 'enum-config',
                    'module' => 'KBSDocuments',
                    'key' => 'languages',
                ),
                array(
                    'label' => 'LBL_DATE_ENTERED',
                    'enabled' => true,
                    'default' => true,
                    'name' => 'date_entered',
                    'readonly' => true,
                ),
                array(
                    'label' => 'LBL_STATUS',
                    'enabled' => true,
                    'default' => true,
                    'name' => 'status',
                    'type' => 'status',
                ),
                array(
                    'name' => 'active_date',
                    'vname' => 'LBL_PUBLISH_DATE',
                    'type' => 'date',
                    'default' => true,
                    'enabled' => true,
                ),
                array(
                    'name' => 'exp_date',
                    'vname' => 'LBL_EXP_DATE',
                    'type' => 'date',
                    'default' => true,
                    'enabled' => true,
                ),
                array(
                    'name' => 'tags',
                    'label' => 'LBL_TAGS',
                    'default' => true,
                    'enabled' => true,
                    'type' => 'tags',
                    'sortable' => false,
                ),
            ),
        ),
    ),
    'orderBy' => array(
        'field' => 'date_entered',
        'direction' => 'desc',
    ),
);
