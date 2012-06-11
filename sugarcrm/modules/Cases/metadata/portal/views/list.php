<?php
$viewdefs['Cases']['portal']['view']['list'] = array(
    'panels' =>
    array(
        0 =>
        array(
            'label' => 'LBL_PANEL_1',
            'fields' =>
            array(
                0 =>
                array(
                    'name' => 'case_number',
                    'label' => 'ID',
                    'enabled' => true,
                    'sorting' => true,
                    'width' =>  8
                ),
                1 =>
                array(
                    'name' => 'name',
                    'label' => 'Title',
                    'enabled' => true,
                    'link' => true,
                    'sorting' => true,
                    'width' =>  49
                ),
                2 =>
                array(
                    'name' => 'status',
                    'label' => 'Status',
                    'enabled' => true,
                    'sorting' => true,
                    'width' =>  17
                ),
                3 =>
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


