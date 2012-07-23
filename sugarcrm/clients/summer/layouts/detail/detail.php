<?php
require_once('clients/summer/SideBarLayout.php');
$layout = new SideBarLayout();
$layout->push('top', array('view'=>'subnav'));
$layout->push('main', array('view'=>'detail'));
$layout->push('side', array('layout'=>'sidebar'));
$layout->push('side', array('view'=>'maps'));
$viewdefs['summer']['layout']['detail'] = $layout->getLayout();
