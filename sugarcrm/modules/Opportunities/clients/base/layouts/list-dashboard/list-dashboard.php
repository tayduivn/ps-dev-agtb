<?php

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

$viewdefs['Opportunities']['base']['layout']['list-dashboard'] = array(
    'metadata' =>
    array(
        'components' =>
        array(
            array(
                'rows' =>
                array(
                    array(
                        array(
                            'view' =>
                            array(
                                'name' => 'dashablelist',
                                'label' => 'My Accounts',
                                'display_columns' =>
                                array(
                                    'name',
                                    'billing_address_country',
                                    'billing_address_city',
                                ),
                                'my_items' => '1',
                                'display_rows' => 5,
                            ),
                            'context' =>
                            array(
                                'module' => 'Accounts',
                            ),
                            'width' => 12,
                        ),
                    ),
                    array(
                        array(
                            'view' =>
                            array(
                                'name' => 'dashablelist',
                                'label' => 'My Contacts',
                                'display_columns' =>
                                array(
                                    'full_name',
                                    'account_name',
                                    'email',
                                    'phone_work',
                                ),
                                'my_items' => '1',
                            ),
                            'context' =>
                            array(
                                'module' => 'Contacts',
                            ),
                            'width' => 12,
                        ),
                    ),
                ),
                'width' => 12,
            ),
        ),
    ),
    'name' => 'My Dashboard',
);

