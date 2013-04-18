<?php
$layout = MetaDataManager::getLayout('SideBarLayout');
$layout->push('main', array('view'=>'record'));
$layout->push('preview', array('layout' => 'preview'));
$viewdefs['KBDocuments']['portal']['layout']['record'] = $layout->getLayout();
