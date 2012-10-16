<?php

$layout = MetaDataManager::getLayout('SideBarLayout');
$layout->push('main', array('layout'=>'list-cluster'));
$layout->push('side', array('layout'=>'list-sidebar'));
$viewdefs['summer']['layout']['records'] = $layout->getLayout();
