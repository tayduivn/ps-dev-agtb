<?php
$viewdefs['Cases']['portal']['view']['test'] = array(
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
                    'label' => 'Case Number',
                    'class' => 'foo',
                    'default' => true,
                    'enabled' => true,
                ),
                1 =>
                array(
                    'name' => 'name',
                    'label' => 'Name',
                    'default' => true,
                    'enabled' => true,
                ),
                2 =>
                array(
                    'name' => 'status',
                    'label' => 'Status',
                    'default' => true,
                    'enabled' => true,
                ),
                3 =>
                array(
                    'name' => 'date_modified',
                    'label' => 'Modifed Date',
                    'default' => true,
                    'enabled' => true,
                ),
            ),
        ),
    ),
);
