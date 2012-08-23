<?php


require_once('clients/summer/SideBarLayout.php');
$layout = new SideBarLayout();
$layout->push('main', array('view'=>'list-top'));
$layout->push('main', array('view'=>'filter'));
$layout->push('main', array('view'=>'list'));
$layout->push('main', array('view'=>'list-bottom'));
$layout->push('side', array('layout'=>'list-sidebar'));
$viewdefs['summer']['layout']['list'] = $layout->getLayout();
