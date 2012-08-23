<?php
$viewdefs['Leads']['base']['layout']['convert'] = array(
    'type' => 'convert',
    'components' => array(
        0 => array(
            'id' => 'convertheader-omg',
            'view' => 'convertheader',
        ),
        1 => array(
            'name' => 'accordion',
            'id' => 'accordion-omg',
            'layout' =>

            array(
                'type' => 'accordion',
                'components' =>
                array(
                    0 => array(
                        'view' => 'accordion-panel',
                        'context' => array(
                            'module' => 'Prospects',
                        ),
                    ),
                    1 => array(
                        'view' => 'accordion-panel',
                        'context' => array(
                            'module' => 'ProspectLists',
                        ),
                    ),
                    2 => array(
                        'view' => 'accordion-panel',
                        'context' => array(
                            'module' => 'ProspectLists',
                        ),
                    ),
                    3 => array(
                        'view' => 'accordion-panel',
                        'context' => array(
                            'module' => 'ProspectLists',
                        ),
                    ),
                    4 => array(
                        'view' => 'list'
                    ),
                    5 => array(
                        'view' => 'list',
                        'context' => array(
                            'module' => 'Prospects'
                        ),
                    ),
                    6 => array(
                        'view' => 'list',
                        'context' => array(
                            'module' => 'ProspectLists',
                        ),
                    ),
                ),
            ),
        ),
        2 => array(
            'view' => 'convertbottom',
        ),

    )
);