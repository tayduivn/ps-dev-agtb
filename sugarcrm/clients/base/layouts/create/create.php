<?php

$layout = MetaDataManager::getLayout('SideBarLayout');
$layout->push('main', array('view'=>'create'));
$layout->push('side', array('layout'=>'sidebar'));
$viewdefs['base']['layout']['create'] = $layout->getLayout();