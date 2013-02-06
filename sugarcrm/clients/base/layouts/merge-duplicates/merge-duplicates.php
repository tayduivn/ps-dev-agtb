<?php

$layout = MetaDataManager::getLayout('SideBarLayout');
$layout->push('main', array('view'=>'merge-duplicates-headerpane'));
$layout->push('main', array('view'=>'merge-duplicates'));
$layout->push('side', array('layout'=>'sidebar'));
$layout->push('preview', array('layout' => 'preview'));
$viewdefs['base']['layout']['merge-duplicates'] = $layout->getLayout();
