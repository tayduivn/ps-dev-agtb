<?php

require_once('clients/summer/SideBarLayout.php');
$layout = new SideBarLayout();
$layout->push('top', array('view'=>'subnav'));
$layout->push('main',array('view'=>'activitystream'));
$layout->push('main',array('view'=>'agenda'));
$layout->push('side',array('view'=>'recommended_contacts'));
$layout->push('side',array('view'=>'yelp'));
$layout->push('side',array('view'=>'recommended_invites'));
$viewdefs['summer']['layout']['dashboard'] = $layout->getLayout();
$GLOBALS['log']->fatal('IKEA: the dashboard '.print_r($viewdefs,true));