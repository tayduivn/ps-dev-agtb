<?php

$layout = MetaDataManager::getLayout('SideBarLayout');
$layout->push('main', array('view'=>'record'));
$layout->push('main', array('layout'=>'subpanel'));
$layout->push('side', array('layout'=>'sidebar'));
$layout->push('preview', array('layout' => 'preview'));
$viewdefs['base']['layout']['record'] = $layout->getLayout();
