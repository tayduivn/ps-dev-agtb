<?php
$viewdefs['Bugs']['portal']['view']['detail'] = array(
    'buttons' =>
    array(
        0 =>
        array(
            'name' => 'edit_button',
            'type' => 'button',
            'label' => 'Edit',
            'value' => 'edit',
            'route' =>
            array(
                'action' => 'edit',
            ),
            'primary' => true,
        ),
    ),
    'panels' =>
    array(
        0 =>
        array(
            'label' => 'Details',
            'fields' =>
            array(
                0 =>
                array(
                    'name' => 'bug_number',
                    'label' => 'Bug Number',
                    'class' => 'foo',
                ),
                1 =>
                array(
                    'name' => 'name',
                    'label' => 'Name',
                ),
                2 =>
                array(
                    'name' => 'status',
                    'label' => 'Status',
                ),
                3 =>
                array(
                    'name' => 'description',
                    'label' => 'Description',
                ),
                4 =>
                array(
                    'name' => 'date_modified',
                    'label' => 'Modifed Date',
                ),
            ),
        ),
    ),
);
