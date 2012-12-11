<?php
$layout = MetaDataManager::getLayout('SideBarLayout');
$layout->push('main', array('view'=>'convert-headerpane'));
$layout->push('main', array('layout'=>'convert-main'));
$layout->push('side', array('layout'=>'convert-sidebar'));
$layout->push('preview', array('layout' => 'preview'));
$viewdefs['Leads']['base']['layout']['convert'] = $layout->getLayout();