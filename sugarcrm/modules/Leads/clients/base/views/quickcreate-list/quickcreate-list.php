<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

$viewdefs['Leads']['base']['view']['quickcreate-list'] = array(
    'panels' => array(
        array(
            'fields' => array(
                array(
                    'name'    => 'name',
                    'label'   => 'LBL_LIST_NAME',
                    'orderBy' => 'last_name',
                ),
                array(
                    'name'  => 'status',
                    'label' => 'LBL_LIST_STATUS',
                ),
                array(
                    'name'  => 'account_name',
                    'label' => 'LBL_LIST_ACCOUNT_NAME',
                ),
                array(
                    'name'  => 'phone_work',
                    'label' => 'LBL_LIST_PHONE',
                ),
                array(
                    'name'     => 'email1',
                    'label'    => 'LBL_LIST_EMAIL_ADDRESS',
                    'sortable' => false,
                ),
                array(
                    'name'  => 'assigned_user_name',
                    'label' => 'LBL_LIST_ASSIGNED_USER',
                ),
                array (
                    'name'  => 'date_entered',
                    'label' => 'LBL_DATE_ENTERED',
                ),
            ),
        ),
    ),
);
