<?php

$layout = new SideBarLayout();
$layout->push('main',array('view'=>'activitystream'));
$layout->push('side',array('view'=>'agenda'));

#$layout->push('side',array('view'=>'yelp'));
$layout->push('side',array('view'=>'gdrive'));
$layout->push('side',array('view'=>'recommended_contacts'));
$layout->push('side',array('view'=>'recommended_invites'));
//$layout->push('side',array('view'=>'preview'));
$viewdefs['summer']['layout']['dashboard'] = $layout->getLayout();
