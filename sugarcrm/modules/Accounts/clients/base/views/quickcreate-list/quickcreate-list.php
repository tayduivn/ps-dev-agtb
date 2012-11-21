<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$viewdefs['Accounts']['base']['view']['quickcreate-list'] = array(
    'panels' => array(
        array(
            'fields' => array(
                array(
                    'name'    => 'name',
                    'label'   => 'LBL_LIST_ACCOUNT_NAME',
                ),
                array(
                    'name'  => 'billing_address_city',
                    'label' => 'LBL_LIST_CITY',
                ),
                array(
                    'name'  => 'billing_address_country',
                    'label' => 'LBL_BILLING_ADDRESS_COUNTRY',
                ),
                array(
                    'name'     => 'email1',
                    'label'    => 'LBL_LIST_EMAIL_ADDRESS',
                    'sortable' => false,
                ),
            ),
        ),
    ),
);
