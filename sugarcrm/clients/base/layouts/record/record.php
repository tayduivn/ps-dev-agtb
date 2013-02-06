<?php

$subpanel = MetaDataManager::getLayout("FilterPanelLayout");
$subpanel->push(array("layout" => "subpanel"));

$layout = MetaDataManager::getLayout('SideBarLayout');
$layout->push('main', array(
    'layout' => array(
        'type' => 'drawer',
        'showEvent' => array(
            "drawer:selection:fire", 
            "drawer:create:fire",
            "drawer:find-duplicates:fire",
        )
    ),
));
$layout->push('main', array('view'=>'record'));
$layout->push('main', array("layout" => $subpanel->getLayout(true)));
$layout->push('side', array('layout'=>'sidebar'));
$layout->push('preview', array('layout' => 'preview'));
$viewdefs['base']['layout']['record'] = $layout->getLayout();