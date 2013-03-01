<?php

$subpanel = MetaDataManager::getLayout("FilterPanelLayout");
$subpanel->push(array("layout" => "subpanel"));

$layout = MetaDataManager::getLayout('SideBarLayout');
$layout->push('main', array('view'=>'record'));
$layout->push('main', array('view'=>'convert-results'));
$layout->push('main', array("layout" => $subpanel->getLayout(true)));
$layout->push('side', array('layout'=>'sidebar'));
$layout->push('preview', array('layout' => 'preview'));
$viewdefs['Prospects']['base']['layout']['record'] = $layout->getLayout();
