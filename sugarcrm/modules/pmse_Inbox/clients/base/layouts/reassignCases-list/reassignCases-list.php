<?php
$module_name = 'pmse_Inbox';
$viewdefs[$module_name]['base']['layout']['reassignCases-list'] = array(
    'components' =>
        array(
            /*array(
                'view' => 'massupdate',
            ),
            array(
                'view' => 'massaddtolist',
            ),*/
            array(
                'view' => 'reassignCases-list',
                'primary' => true,
            ),
            array(
                'view' => 'list-bottom',
            ),
        ),
    'type' => 'simple',
    'name' => 'reassignCases-list',
    'span' => 12,
);
