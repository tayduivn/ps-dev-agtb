<?php
//FILE SUGARCRM flav=pro ONLY
$module_name = 'PdfManager';
$dependencies[$module_name]['read_only_base_module_edition'] = array (
    'hooks' => array("edit", "view"),
    'trigger' => 'true',
    'triggerFields' => array(
        'id'
    ),
    'onload' => true,
    'actions' => array(
        array(
            'name' => 'ReadOnly',
            'params' => array(
                'target' => 'base_module',
                'value' => 'not(equal($record, ""))'
            )
        ),
    ),
    'notActions' => array(),
);
