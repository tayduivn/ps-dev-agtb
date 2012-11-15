<?php

$layout = MetaDataManager::getLayout('SideBarLayout');
$layout->push('main', array('layout'=>'list-cluster'));
$layout->push('side', array('layout'=>'list-sidebar'));
$layout->push('preview', array('layout'=>'preview'));
$viewdefs['base']['layout']['records'] = $layout->getLayout();