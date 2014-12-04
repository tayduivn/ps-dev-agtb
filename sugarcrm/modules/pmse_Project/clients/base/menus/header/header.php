<?php


$moduleName = 'pmse_Project';
$viewdefs[$moduleName]['base']['menu']['header'] = array(
    array(
        'route' => "#$moduleName/create",
        'label' => 'LNK_PMSE_PROCESS_DEFINITIONS_NEW_RECORD',
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
        'route'=>'#'.$moduleName.'/layout/project-import',
        'label' =>'LNK_PMSE_PROCESS_DEFINITIONS_IMPORT_RECORD',
        'acl_action'=>'import',
        'acl_module'=>$moduleName,
        'icon' => 'fa-arrow-circle-o-up',
    ),
);
