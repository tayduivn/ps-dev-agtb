<?php
$layout = MetaDataManager::getLayout('SideBarLayout');
$layout->push('main', array('view'=>'leadrecord'));
$layout->push('main', array('layout'=>'tabbed-layout'));
$layout->push('side', array('layout'=>'sidebar'));
$viewdefs['Leads']['base']['layout']['record'] = $layout->getLayout();
