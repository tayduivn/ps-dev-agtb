<?php

$layout = MetaDataManager::getLayout('SideBarLayout');
$layout->push('main', array('view'=>'merge-duplicates-headerpane'));
$layout->push('side', array('layout'=>'sidebar'));
$viewdefs['base']['layout']['merge-duplicates'] = $layout->getLayout();
