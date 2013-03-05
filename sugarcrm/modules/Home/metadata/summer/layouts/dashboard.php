<?php

$layout = MetaDataManager::getLayout('SideBarLayout');
$layout->push('top');
$layout->push('main', array('view'=>'agenda'));
$layout->push('side', array('view'=>'recommended-contacts'), array('view'=>'yelp'),array('view'=>'recommended-invites'));
$viewdefs['Home']['summer']['layout']['dashboard'] = $layout->getLayout();
