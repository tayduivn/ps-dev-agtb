<?php

$layout = MetaDataManager::getLayout('SideBarLayout');
$layout->push('main', array('view' => 'record'));
$layout->push('side', array('layout' => 'new-sidebar'));
$viewdefs['base']['layout']['newrecord'] = $layout->getLayout();
