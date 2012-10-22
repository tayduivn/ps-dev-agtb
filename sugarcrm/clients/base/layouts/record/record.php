<?php

$layout = MetaDataManager::getLayout('SideBarLayout');
$layout->push('main', array('view'=>'headerpane'));
$layout->push('main', array('view'=>'record'));
$layout->push('main', array('layout'=>'tabbed-layout'));
$layout->push('side', array('layout'=>'sidebar'));
$viewdefs['base']['layout']['record'] = $layout->getLayout();
