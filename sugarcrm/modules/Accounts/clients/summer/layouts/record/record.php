<?php

require_once('clients/summer/SideBarLayout.php');
$layout = new SideBarLayout();
$layout->push('top', array('view' => 'subnav'));
$layout->push('main', array('view' => 'record'));
$layout->push('main', array('layout' => 'tabbed-layout'));
$layout->push('side', array('layout' => 'sidebar'));

$viewdefs['Accounts']['summer']['layout']['record'] = $layout->getLayout();
