<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

$viewdefs['KBSContents']['base']['view']['config'] = array(
    'columns' => 2,
    'configModule' => 'KBSDocuments',
    'panels' => array(
        array(
            'label' => 'LBL_ADMIN_LABEL_LANGUAGES',
            'fields' => array(
                array(
                    'name' => 'languages',
                    'type' => 'multitext',
                    'searchBarThreshold' => 5,
                    'label' => 'LBL_EDIT_LANGUAGES',
                    'default' => false,
                    'enabled' => true,
                    'view' => 'edit',
                    'span' => 6
                ),
            ),
        ),
    )
);
