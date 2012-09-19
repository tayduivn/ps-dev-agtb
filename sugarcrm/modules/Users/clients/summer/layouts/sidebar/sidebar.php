<?php
require_once('clients/summer/SideBarLayout.php');
$layout = new SideBarLayout();

$layout->push('main', array('view'=>'twitter'));
$layout->push('main', array('view'=>'maps'));
$layout->push('main', array('view'=>'todo-list'));
$layout->push('main', array('view'=>'linkedin'));
$layout->push('main', array('view'=>'gplus'));
$viewdefs['Users']['summer']['layout']['sidebar'] = $layout->getLayout();