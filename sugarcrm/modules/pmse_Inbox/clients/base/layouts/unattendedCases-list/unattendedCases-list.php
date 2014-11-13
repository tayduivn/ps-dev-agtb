<?php
$module_name = 'pmse_Inbox';
$viewdefs[$module_name]['base']['layout']['unattendedCases-list'] = array(
    'components' =>
        array(
            /*array(
                'view' => 'massupdate',
            ),
            array(
                'view' => 'massaddtolist',
            ),*/
            array(
                'view' => 'unattendedCases-list',
                'primary' => true,
            ),
            array(
                'view' => 'list-bottom',
            ),
        ),
    'type' => 'simple',
    'name' => 'unattendedCases-list',
    'span' => 12,
);
