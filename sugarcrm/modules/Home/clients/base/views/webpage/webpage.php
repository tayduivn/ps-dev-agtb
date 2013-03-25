<?php

$viewdefs['Home']['base']['view']['webpage'] = array(
    'dashlets' => array(
        array(
            'name' => 'Web Page',
            'description' => 'Web Page',
            'config' => array(
                'url' => 'http://www.sugarcrm.com',
            ),
            'preview' => array(
                'title' => 'Web Page',
                'url' => 'www.sugarcrm.com',
                'limit' => '3',
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
                'type' => 'base' ,
                'name' => 'height',
                'label' => 'Height',
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
