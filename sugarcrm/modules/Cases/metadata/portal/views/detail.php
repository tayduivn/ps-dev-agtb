<?php
$viewdefs ['Cases']['portal']['view']['detail'] =
    array(
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
                        'name' => 'case_number',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    array(
                        'name' => 'name',
                        'displayParams' =>
                        array(
                            'colspan' => 2,
                        ),
                    ),
                    'date_entered',
                    'status',
                    'priority',
                    'type',
                    'date_modified',
                    'modified_by_name',
                    'created_by_name',
                    array(
                        'name' => 'description',
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
