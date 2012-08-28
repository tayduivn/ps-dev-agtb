<?php
require_once('clients/summer/SideBarLayout.php');
$layout = new SideBarLayout();

$layout->push('main', array('view'=>'imagesearch'));

#$layout->push('main', array('view'=>'maps'));
#$layout->push('main', array('view'=>'twitter'));
#$layout->push('main', array('view'=>'facebook'));
$layout->push('main', array('layout'=>'sublist','context'=>array('link'=>'opportunities')));
$viewdefs['Contacts']['summer']['layout']['sidebar'] = $layout->getLayout();
