<?php


$moduleName = 'pmse_Inbox';
$viewdefs[$moduleName]['base']['menu']['header'] = array(
//    array(
//        'route' => "#$moduleName/create",
//        'label' => 'LNK_NEW_RECORD',
//        'acl_action' => 'create',
//        'acl_module' => $moduleName,
//        'icon' => 'icon-plus',
//    ),
    array(
        'route' => "#$moduleName",
        'label' => 'LNK_LIST',
        'acl_action' => 'list',
        'acl_module' => $moduleName,
        'icon' => 'icon-reorder',
    ),
    array(
        'route'=>'#'.$moduleName.'/layout/casesList',
        'label' =>'LBL_CASES_LIST_PMSE',
        'acl_action'=>'list',
        'acl_module'=>$moduleName,
        'icon' => 'icon-reorder',
    ),
    array(
        'route'=>'#'.$moduleName.'/layout/unattendedCases',
        'label' =>'LBL_UNATTENDED_CASES_TITLE',
        'acl_action'=>'list',
        'acl_module'=>$moduleName,
        'icon' => 'icon-reorder',
    ),
);