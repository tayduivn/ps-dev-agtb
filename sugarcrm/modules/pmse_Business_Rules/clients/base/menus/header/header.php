<?php

$moduleName = 'pmse_Business_Rules';
$viewdefs[$moduleName]['base']['menu']['header'] = array(
    array(
        'route' => "#$moduleName/create",
        'label' => 'LNK_PMSE_BUSINESS_RULES_NEW_RECORD',
        'acl_action' => 'create',
        'acl_module' => $moduleName,
        'icon' => 'fa-plus',
    ),
    array(
        'route' => "#$moduleName",
        'label' => 'LNK_LIST',
        'acl_action' => 'list',
        'acl_module' => $moduleName,
        'icon' => 'fa-bars',
    ),
    array(
        'route'=>'#'.$moduleName.'/layout/businessrules-import',
        'label' =>'LNK_PMSE_BUSINESS_RULES_IMPORT_RECORD',
        'acl_action'=>'upload',
        'acl_module'=>$moduleName,
        'icon' => 'fa-upload',
    ),
);
