<?php

require_once('clients/summer/SideBarLayout.php');
$layout = new SideBarLayout();
$layout->push('top', array('view' => 'subnav'));
$layout->push('main', array('view' => 'record'));
$layout->push('side', array('layout' => 'new-sidebar'));
$viewdefs['summer']['layout']['newrecord'] = $layout->getLayout();