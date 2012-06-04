<?php
$viewdefs['Bugs']['portal']['view']['table'] = array(
    'panels' =>
    array(
        0 =>
        array(
            'label' => 'LBL_PANEL_1',
            'fields' =>
            array(
                0 =>
                array(
                    'name' => 'bug_number',
                    'label' => 'ID',
                    'class' => 'foo',
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
                    'name' => 'priority',
                    'label' => 'Priority',
                    'enabled' => true,
                    'sorting' => true,
                    'width' =>  13
                ),
                3 =>
                array(
                    'name' => 'status',
                    'label' => 'Status',
                    'enabled' => true,
                    'sorting' => true,
                    'width' =>  17
                ),
            ),
        ),
    ),
);


