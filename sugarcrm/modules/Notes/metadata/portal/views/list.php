<?php
$viewdefs['Notes']['portal']['view']['list'] = array(
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
                    'enabled' => true,
                    'link' => true,
                    'sorting' => true,
                    'width' =>  49
                ),
                1 =>
                array(
                    'name' => 'date_modified',
                    'label' => 'Modifed Date',
                    'default' => true,
                    'enabled' => true,
                    'sorting' => true,
                    'width' => 17
                ),
            ),
        ),
    ),
);


