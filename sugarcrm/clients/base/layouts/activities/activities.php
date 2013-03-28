<?php

$layout = MetaDataManager::getLayout('SideBarLayout');

$layout->push('main', array('view' => 'list-headerpane'));

$listLayout = MetaDataManager::getLayout("FilterPanelLayout", array("default" => "activitystream"));
$layout->push('main', array('layout' => $listLayout->getLayout(true)));

$layout->push('side', array('layout' => 'list-sidebar'));
$layout->push('dashboard', array('layout' => 'dashboard', 'context' => array(
    'forceNew' => true,
    'module' => 'Home',
)));
$layout->push('preview', array('layout' => 'preview'));
$viewdefs['base']['layout']['activities'] = $layout->getLayout();
