<?php
$viewdefs ['Bugs']['portal']['view']['activity'] =
    array(
        'buttons' =>
        array(),
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
                    'id',
                    'date_entered',
                    'description',
                ),
            ),
        ),
    );
?>
