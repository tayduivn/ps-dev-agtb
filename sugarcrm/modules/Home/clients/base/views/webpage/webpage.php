<?php

$viewdefs['Home']['base']['view']['webpage'] = array(
    'dashlets' => array(
        array(
            'name' => 'Web Page',
            'description' => 'Web Page',
            'config' => array(
                'url' => 'http://www.sugarcrm.com',
                'module' => 'Home',
                'limit' => 3,
            ),
            'preview' => array(
                'title' => 'Web Page',
                'url' => 'www.sugarcrm.com',
                'limit' => '3',
                'module' => 'Home',
            ),
        ),
    ),
    'config' => array(
        'fields' => array(
            array(
                'type' => 'iframe',
                'name' => 'url',
                'label' => "URL",
            ),
            array(
                'name' => 'limit',
                'label' => 'LBL_DASHLET_CONFIGURE_DISPLAY_ROWS',
                'type' => 'enum',
                'options' => 'dashlet_webpage_limit_options',
            ),
        ),
    ),
    'view_panel' => array(
        array(
            'type' => 'iframe',
            'name' => 'url',
            'label' => "URL",
            'width' => '100%',

        ),
    ),
);
