<?php

$layout = MetaDataManager::getLayout('SideBarLayout');
$layout->push('main', array('view'=>'create'));
$layout->push('preview', array('layout' => 'preview'));
$viewdefs['base']['layout']['create'] = $layout->getLayout();