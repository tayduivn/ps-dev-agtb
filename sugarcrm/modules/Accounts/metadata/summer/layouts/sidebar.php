<?php
require_once('clients/summer/SideBarLayout.php');
$layout = new SideBarLayout();
$layout->push('main', array('view'=>'crunchbase'));
$layout->push('main', array('view'=>'gplus'));
$layout->push('main', array('view'=>'maps'));
$layout->push('main', array('view'=>'news'));
$layout->push('main', array('view'=>'twitter'));
$layout->push('main', array('view'=>'imagesearch'));


$viewdefs['Accounts']['summer']['layout']['sidebar'] = $layout->getLayout();
