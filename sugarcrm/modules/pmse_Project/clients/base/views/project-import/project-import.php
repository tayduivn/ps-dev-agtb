<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}
$module_name = 'pmse_Project';
$viewdefs[$module_name]['base']['view']['project-import'] = array(
    'panels' => array(
        array(
            'fields' => array(
                array(
                    'name' => 'project_import',
                    'type' => 'file',
                    'view' => 'edit',
                )
            ),
        ),
    )
);
