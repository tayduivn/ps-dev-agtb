<?php
$viewdefs['base']['view']['interactions'] = array(
    'dashlets' => array(
        array(
            'name' => 'Interactions',
            'description' => 'Interactions belongs to Accounts, Contacts, Leads, and Emails',
            'config' => array(

            ),
            'preview' => array(

            ),
            'filter' => array(
                'module' => array(
                    'Accounts',
                    'Contacts',
                    'Leads',
                    'Opportunities',
                ),
                'view' => 'record'
            )
        ),
    ),
    'panels' => array(
        array(
            'name' => 'panel_body',
            'columns' => 2,
            'labelsOnTop' => true,
            'placeholders' => true,
            'fields' => array(
                array(
                    'name' => 'filter_duration',
                    'label' => 'Filter',
                    'type' => 'enum',
                    'options' => 'interactions_options'
                ),
            ),
        ),
    ),
    'filter_duration' => array(
        array(
            'name' => 'filter_duration',
            'label' => 'Filter',
            'type' => 'enum',
            'options' => 'interactions_options'
        ),
    )
);
