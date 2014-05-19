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
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

$viewdefs['Prospects']['base']['view']['recordlist'] = array(
    'selection' => array(
        'type' => 'multi',
        'actions' => array(
            array(
                'name' => 'mass_email_button',
                'type' => 'mass-email-button',
                'label' => 'LBL_EMAIL_COMPOSE',
                'primary' => true,
                'events' => array(
                    'click' => 'list:massaction:hide',
                ),
                'acl_module' => 'Emails',
                'acl_action' => 'edit',
                'related_fields' => array('name', 'email'),
            ),
            array(
                'name' => 'edit_button',
                'type' => 'button',
                'label' => 'LBL_MASS_UPDATE',
                'primary' => true,
                'events' => array(
                    'click' => 'list:massupdate:fire',
                ),
                'acl_action' => 'massupdate',
            ),
            array(
                'name' => 'merge_button',
                'type' => 'button',
                'label' => 'LBL_MERGE',
                'primary' => true,
                'events' => array(
                    'click' => 'list:mergeduplicates:fire',
                ),
                'acl_action' => 'edit',
            ),
            array(
                'name' => 'calc_field_button',
                'type' => 'button',
                'label' => 'LBL_UPDATE_CALC_FIELDS',
                'events' => array(
                    'click' => 'list:updatecalcfields:fire',
                ),
                'acl_action' => 'massupdate',
            ),
            array(
                'name' => 'addtolist_button',
                'type' => 'button',
                'label' => 'LBL_ADD_TO_PROSPECT_LIST_BUTTON_LABEL',
                'primary' => true,
                'events' => array(
                    'click' => 'list:massaddtolist:fire',
                ),
                'acl_module' => 'ProspectLists',
                'acl_action' => 'edit',
            ),
            array(
                'name' => 'delete_button',
                'type' => 'button',
                'label' => 'LBL_DELETE',
                'acl_action' => 'delete',
                'primary' => true,
                'events' => array(
                    'click' => 'list:massdelete:fire',
                ),
            ),
            array(
                'name' => 'export_button',
                'type' => 'button',
                'label' => 'LBL_EXPORT',
                'acl_action' => 'export',
                'primary' => true,
                'events' => array(
                    'click' => 'list:massexport:fire',
                ),
            ),
        ),
    ),
);
