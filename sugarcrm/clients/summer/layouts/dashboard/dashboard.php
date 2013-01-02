<?php

$layout = MetaDataManager::getLayout('SideBarLayout');
$layout->push('main', array('view'=>'headerpane'));
$layout->push('main', array('layout' => 'subpanel'));
$layout->push('side', array('view' => 'agenda'));
$layout->push('side', array('view' => 'gdrive'));
$layout->push('side', array('view' => 'recommended-contacts'));
$layout->push('side', array('view' => 'recommended-invites'));
$layout->push('preview', array('layout' => 'preview'));
$viewdefs['summer']['layout']['dashboard'] = $layout->getLayout();
