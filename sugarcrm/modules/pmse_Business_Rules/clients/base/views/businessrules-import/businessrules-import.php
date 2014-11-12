<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'pmse_Business_Rules';
$viewdefs[$module_name]['base']['view']['businessrules-import'] = array(
    'panels' => array(
        array(
            'fields' => array(
                array(
                    'name' => 'businessrules_import',
                    'type' => 'file',
                    'view' => 'edit',
                )
            ),
        ),
    )
);
