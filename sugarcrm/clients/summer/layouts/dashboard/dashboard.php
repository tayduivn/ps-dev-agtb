<?php

require_once('clients/summer/SideBarLayout.php');
$layout = new SideBarLayout();
$layout->push('main',array('view'=>'activitystream'));
$layout->push('side',array('view'=>'agenda'));

#$layout->push('side',array('view'=>'yelp'));
$layout->push('side',array('view'=>'recommended_invites'));
$layout->push('side',array('view'=>'untouchables'));
$viewdefs['summer']['layout']['dashboard'] = $layout->getLayout();
