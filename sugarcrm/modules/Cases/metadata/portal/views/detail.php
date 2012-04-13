<?php
$viewdefs['Cases']['portal']['view']['detail'] = array(
    'buttons' =>
    array(
        0 =>
        array(
            'name' => 'edit_button',
            'type' => 'button',
            'label' => 'LBL_EDIT_BUTTON_LABEL',
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
            'label' => 'LBL_DETAILVIEW',
            'fields' =>
            array(
                0 =>
                array(
                    'name' => 'case_number',
                    'label' => 'LBL_CASE_NUMBER',
                    'class' => 'foo',
                ),
                1 =>
                array(
                    'name' => 'name',
                    'label' => 'LBL_SUBJECT',
                ),
                2 =>
                array(
                    'name' => 'status',
                    'label' => 'LBL_LIST_STATUS',
                ),
                3 =>
                array(
                    'name' => 'description',
                    'label' => 'LBL_DESCRIPTION',
                ),
                4 =>
                array(
                    'name' => 'date_modified',
                    'label' => 'LBL_LAST_MODIFIED',
                ),
            ),
        ),
    ),
);
