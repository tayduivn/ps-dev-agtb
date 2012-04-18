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
                        'label' => 'Number',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'priority',
                        'label' => 'Priority',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'product_category',
                        'label' => 'Category',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'status',
                        'label' => 'Status',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'type',
                        'label' => 'Type',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'resolution',
                        'label' => 'Resolution',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'date_entered',
                        'label' => 'Created',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'date_modified',
                        'label' => 'Modified',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'name',
                        'label' => 'Name',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'description',
                        'label' => 'Description',
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