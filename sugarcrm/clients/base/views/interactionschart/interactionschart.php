<?php

$viewdefs['base']['view']['interactionschart'] = array(
    'dashlets' => array(
        array(
            'name' => 'Interactions Chart',
            'description' => 'Displays Account interactions on chart.',
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
    'ui' => array(
        'colors' => array(
            'default' => '#085f94',
            'calls' => '#cce8f6',
            'emailsSent' => '#0092d1',
            'emailsRecv' => '#085f94',
            'meetings' => '#0d3d66',
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
