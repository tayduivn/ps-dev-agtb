<?php

$layout = MetaDataManager::getLayout('SideBarLayout');
$layout->push('main', array('view'=>'headerpane'));
$layout->push('main', array('view'=>'list-top'));
$layout->push('main', array('view'=>'filter'));
$layout->push('main', array('view'=>'list'));
$layout->push('main', array('view'=>'list-bottom'));
$layout->push('side', array('layout'=>'list-sidebar'));
$viewdefs['base']['layout']['list'] = $layout->getLayout();