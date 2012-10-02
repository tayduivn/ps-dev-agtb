<?php

require_once('clients/summer/SideBarLayout.php');
$layout = new SideBarLayout();
$layout->push('top');
$layout->push('main', array('view'=>'activitystream'), array('view'=>'agenda'));
$layout->push('side', array('view'=>'recommended_contacts'),array('view'=>'yelp'),array('view'=>'recommended_invites'));
$viewdefs['Home']['summer']['layout']['dashboard'] = $layout->getLayout();
