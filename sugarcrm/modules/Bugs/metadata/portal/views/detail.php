<?php
$viewdefs ['Bugs']['portal']['view']['detail'] =
    array(
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
        'templateMeta' =>
        array(
            'maxColumns' => '2',
            'widths' =>
            array(
                array(
                    'label' => '10',
                    'field' => '30',
                ),
                array(
                    'label' => '10',
                    'field' => '30',
                ),
            ),
            'useTabs' => false,
        ),
        'panels' =>
        array(
            array(
                'label' => 'default',
                'fields' =>
                array(
                    array(
                        'name' => 'bug_number',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    'status',
                    'priority',
                    'source',
                    'product_category',
                    'resolution',
                    'type',
                    'date_modified',
                    'modified_by_name',
                    'created_by_name',
                    'date_entered',
                    array(
                        'name' => 'name',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'description',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'work_log',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                ),
            ),
        ),
    );
?>
