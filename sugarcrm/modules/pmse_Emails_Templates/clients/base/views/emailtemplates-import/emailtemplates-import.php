<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

$module_name = 'pmse_Emails_Templates';
$viewdefs[$module_name]['base']['view']['emailtemplates-import'] = array(
    'panels' => array(
        array(
            'fields' => array(
                array(
                    'name' => 'emailtemplates_import',
                    'type' => 'file',
                    'view' => 'edit',
                )
            ),
        ),
    )
);
