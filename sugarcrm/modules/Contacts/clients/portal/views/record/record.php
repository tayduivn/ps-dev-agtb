<?php
//FILE SUGARCRM flav=ent || flav=sales ONLY

/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

$viewdefs['Contacts']['portal']['view']['record'] = array(
    'panels' => array(
        array(
            'name' => 'panel_header',
            'header' => true,
            'fields' => array(
                array(
                    'name' => 'picture',
                    'type' => 'image',
                    'width' => 42,
                    'height' => 42,
                    'dismiss_label' => true,
                ),
                array(
                    'name' => 'full_name',
                    'type' => 'fieldset-with-labels',
                    'fields' => array('first_name', 'last_name'),
                ),
            ),
        ),
        array(
            'name' => 'panel_body',
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' =>
            array(
                array(
                    'name' => 'title',
                    'displayParams' =>
                    array(
                        'colspan' => 2,
                    ),
                ),
                array(
                    'name' => 'email',
                    'displayParams' =>
                    array(
                        'colspan' => 2,
                    ),
                ),
                array(
                    'name' => 'portal_password',
                    'type' => 'url',
                    'label' => 'LBL_CONTACT_EDIT_PASSWORD',
                    'class' => 'password',
                    'view' => 'detail',
                    'displayParams' =>
                    array(
                        'colspan' => 2,
                    ),
                ),
                array(
                    'name' => 'phone_work',
                    'displayParams' =>
                    array(
                        'colspan' => 2,
                    ),
                ),
                array(
                    'name' => 'primary_address_street',
                    'displayParams' =>
                    array(
                        'colspan' => 2,
                    ),
                ),
                array(
                    'name' => 'primary_address_city',
                    'displayParams' =>
                    array(
                        'colspan' => 2,
                    ),
                ),
                array(
                    'name' => 'primary_address_state',
                    'options' => 'state_dom',
                    'displayParams' =>
                    array(
                        'colspan' => 2,
                    ),
                ),
                array(
                    'name' => 'primary_address_postalcode',
                    'displayParams' =>
                    array(
                        'colspan' => 2,
                    ),
                ),
                array (
                    'name' => 'primary_address_country',
                    'options' => 'countries_dom',
                    'displayParams' =>
                    array(
                        'colspan' => 2,
                    ),
                ),
                array (
                    'name' => 'preferred_language',
                    'options' => 'available_language_dom',
                ),
            ),
        ),
    ),
);
