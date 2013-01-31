<?php

$layout = MetaDataManager::getLayout('SideBarLayout');
$layout->push('main', array('view'=>'find-duplicates-headerpane'));
$layout->push('main', array('layout'=>'dupecheck'));
$layout->push('side', array('layout'=>'sidebar'));
$layout->push('preview', array('layout' => 'preview'));
$viewdefs['base']['layout']['find-duplicates'] = $layout->getLayout();
