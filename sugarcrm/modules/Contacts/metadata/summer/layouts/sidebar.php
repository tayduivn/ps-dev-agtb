<?php
require_once('clients/summer/SideBarLayout.php');
$layout = new SideBarLayout();

$layout->push('main', array('view'=>'imagesearch'));

$layout->push('main', array('view'=>'maps'));
$layout->push('main', array('view'=>'twitter'));
$layout->push('main', array('view'=>'facebook'));
$viewdefs['Contacts']['summer']['layout']['sidebar'] = $layout->getLayout();
