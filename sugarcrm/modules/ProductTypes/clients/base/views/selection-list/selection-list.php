<?php
$viewdefs['ProductTypes']['base']['view']['selection-list'] = array(
    'favorites' => false,
    'panels' => array(
        array(
            'name' => 'panel_header',
            'fields' => array(
                array(
                    'name' => 'name',
                    'link' => true,
                    'enabled' => true,
                    'default' => true,
                ),
                array (
                        'label' => 'LBL_DATE_MODIFIED',
                        'enabled' => true,
                        'default' => true,
                        'name' => 'date_modified',
                        'readonly' => true,
                ),

            ),
        ),
    ),
);
