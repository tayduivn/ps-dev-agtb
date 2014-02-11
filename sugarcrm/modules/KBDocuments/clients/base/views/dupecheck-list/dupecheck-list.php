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
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 */
$viewdefs['KBDocuments']['base']['view']['dupecheck-list'] = array(
    'panels' => array(
        array(
            'label' => 'LBL_PANEL_DEFAULT',
            'fields' => array(
                array(
                    'name' => 'kbdocument_name',
                    'width' => '45%',
                    'label' => 'LBL_ARTICLE_TITLE',
                    'link' => true,
                    'bwcLink' => false,
                    'enabled' => true,
                    'default' => true,
                ),
                array(
                    'name' => 'views_number',
                    'width' => '10%',
                    'label' => 'LBL_LIST_VIEWING_FREQUENCY',
                    'enabled' => true,
                    'default' => true,
                ),
                array(
                    'name' => 'kbdoc_approver_name',
                    'width' => '10%',
                    'label' => 'LBL_LIST_KBDOC_APPROVER_NAME',
                    'enabled' => true,
                    'default' => true,
                ),
                array(
                    'name' => 'assigned_user_name',
                    'width' => '10%',
                    'label' => 'LBL_ARTICLE_AUTHOR_LIST',
                    'enabled' => true,
                    'default' => true,
                ),
                array(
                    'name' => 'active_date',
                    'label' => 'LBL_DOC_ACTIVE_DATE',
                    'enabled' => true,
                    'width' => '10%',
                    'default' => false,
                ),
                array(
                    'name' => 'exp_date',
                    'label' => 'LBL_DOC_EXP_DATE',
                    'enabled' => true,
                    'width' => '10%',
                    'default' => false,
                ),
                array(
                    'name' => 'date_entered',
                    'label' => 'LBL_DATE_ENTERED',
                    'enabled' => true,
                    'width' => '10%',
                    'default' => false,
                    'readonly' => true,
                ),
                array(
                    'name' => 'kbdocument_revision_number',
                    'label' => 'LBL_KBDOCUMENT_REVISION_NUMBER',
                    'enabled' => true,
                    'width' => '10%',
                    'default' => false,
                ),
            ),
        ),
    ),
);
