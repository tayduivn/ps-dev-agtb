<?php
$viewdefs['Leads']['portal']['view']['list'] = array(
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
                'module' => 'Leads',
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
                    'label' => 'Title',
                    'default' => true,
                    'enabled' => true,
                    'link' => true,
                    'width' => 49
                ),
                1 =>
                array(
                    'name' => 'status',
                    'label' => 'Status',
                    'default' => true,
                    'enabled' => true,
                    'width' => 13
                ),
                2 =>
                array(
                    'name' => 'date_modified',
                    'label' => 'Modifed Date',
                    'default' => true,
                    'enabled' => true,
                    'width' => 17
                ),
            ),
        ),
    ),
);
