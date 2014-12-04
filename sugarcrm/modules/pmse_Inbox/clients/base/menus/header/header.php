<?php


$moduleName = 'pmse_Inbox';
$viewdefs[$moduleName]['base']['menu']['header'] = array(
//    array(
//        'route' => "#$moduleName/create",
//        'label' => 'LNK_NEW_RECORD',
//        'acl_action' => 'create',
//        'acl_module' => $moduleName,
//        'icon' => 'fa-plus',
//    ),
    array(
        'route' => "#$moduleName",
        'label' => 'LNK_LIST',
        'acl_action' => 'list',
        'acl_module' => $moduleName,
        'icon' => 'fa-bars',
    ),
    array(
        'route'=>'#'.$moduleName.'/layout/casesList',
        'label' =>'LBL_PMSE_TITLE_PROCESSESS_LIST',
        'acl_action'=>'list',
        'acl_module'=>$moduleName,
        'icon' => 'fa-bars',
    ),
    array(
        'route'=>'#'.$moduleName.'/layout/unattendedCases',
        'label' =>'LBL_PMSE_TITLE_UNATTENDED_CASES',
        'acl_action'=>'list',
        'acl_module'=>$moduleName,
        'icon' => 'fa-bars',
    ),
);
