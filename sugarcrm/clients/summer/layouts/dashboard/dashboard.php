<?php

$layout = MetaDataManager::getLayout('SideBarLayout');
$layout->push('main', array('layout' => 'subpanel'));
//$layout->push('main', array('view' => 'activitystream'));
$layout->push('side', array('view' => 'agenda'));

$layout->push('side', array('view' => 'gdrive'));
$layout->push('side', array('view' => 'recommended_contacts'));
$layout->push('side', array('view' => 'recommended_invites'));
$viewdefs['summer']['layout']['dashboard'] = $layout->getLayout();
