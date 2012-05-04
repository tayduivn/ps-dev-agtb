<?php
$viewdefs['Notes']['portal']['view']['list'] = array(
    'buttons' =>
    array(
        0 =>
        array(
            'name' => 'show_more_button',
            'type' => 'button',
            'label' => 'Show More',
            'class' => 'loading wide'
        ),
    ),
    'listNav' =>
    array(
        0 =>
        array(
            'name' => 'show_more_button_back',
            'type' => 'navelement',
            'icon' => 'icon-plus',
            'label' => ' ',
            'route' =>
            array(
                'action' => 'create',
                'module' => 'Notes',
            ),
        ),
        1 =>
        array(
            'name' => 'show_more_button_back',
            'type' => 'navelement',
            'icon' => 'icon-chevron-left',
            'label' => ' '
        ),
        2 =>
        array(
            'name' => 'show_more_button_forward',
            'type' => 'navelement',
            'icon' => 'icon-chevron-right',
            'label' => ' '
        ),
    ),
    'panels' =>
    array(
        0 =>
        array(
            'label' => 'LBL_PANEL_1',
            'fields' =>
            array(
                0 =>
                array(
                    'name' => 'name',
                    'default' => true,
                    'enabled' => true,
                    'sorting' => true,
                    'width' =>  8
                ),
                1 =>
                array(
                    'name' => 'description',
                    'default' => true,
                    'enabled' => true,
                    'link' => true,
                    'sorting' => true,
                    'width' => 49
                ),
                2 =>
                array(
                    'name' => 'date_entered',
                    'default' => true,
                    'enabled' => true,
                    'sorting' => true,
                    'width' => 13
                ),
            ),
        ),
    ),
);
