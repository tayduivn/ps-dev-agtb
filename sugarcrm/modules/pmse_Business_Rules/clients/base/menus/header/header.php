<?php

$moduleName = 'pmse_Business_Rules';
$viewdefs[$moduleName]['base']['menu']['header'] = array(
    array(
        'route' => "#$moduleName/create",
        'label' => 'LNK_NEW_RECORD',
        'acl_action' => 'create',
        'acl_module' => $moduleName,
        'icon' => 'icon-plus',
    ),
    array(
        'route' => "#$moduleName",
        'label' => 'LNK_LIST',
        'acl_action' => 'list',
        'acl_module' => $moduleName,
        'icon' => 'icon-reorder',
    ),
    array(
        'route'=>'#'.$moduleName.'/layout/businessrules-import',
        'label' =>'LNK_IMPORT_PMSE_BUSINESS_RULES',
        'acl_action'=>'upload',
        'acl_module'=>$moduleName,
        'icon' => 'icon-upload',
    ),
);