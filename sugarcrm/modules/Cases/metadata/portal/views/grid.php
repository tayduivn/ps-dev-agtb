<?php
$viewdefs['Cases']['portal']['view']['grid'] = array(
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
                    'type' => 'clickToEdit',
                    'cteclass' => 'cte',
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
                4 =>
                array(
                    'name' => 'assigned_user_id',
                    'label' => 'Assigned User',
                    'default' => true,
                    'enabled' => true,
                ),
            ),
        ),
    ),
);
