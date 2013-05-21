<?php
$layout = MetaDataManager::getLayout('SideBarLayout');
$layout->push('main', array('view' => 'list-headerpane'));
$layout->push('main', array('layout' => 'list'));
$layout->push('preview', array('layout' => 'preview'));
$viewdefs['portal']['layout']['records'] = $layout->getLayout();
