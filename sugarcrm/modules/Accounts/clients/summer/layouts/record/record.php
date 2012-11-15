<?php

$layout = MetaDataManager::getLayout('SideBarLayout');
$layout->push('main', array('view' => 'record'));
$layout->push('main', array('layout' => 'tabbed-layout'));
$layout->push('side', array('layout' => 'sidebar'));
$layout->push('preview', array('layout' => 'preview'));
$viewdefs['Accounts']['summer']['layout']['record'] = $layout->getLayout();
