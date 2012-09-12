<?php
require_once('clients/summer/SideBarLayout.php');
$layout = new SideBarLayout();

$layout->push('main', array('view'=>'crunchbase'));
$layout->push('main', array('view'=>'news'));
$layout->push('main', array('view'=>'twitter'));
$layout->push('main', array('view'=>'todo-list'));
$layout->push('main', array('layout'=>'sublist','context'=>array('link'=>'contacts')));
$layout->push('main', array('layout'=>'sublist','context'=>array('link'=>'opportunities')));
$layout->push('main', array('view'=>'maps'));

//$layout->push('main', array('view'=>'facebook'));
//$layout->push('main', array('view'=>'linkedin'));
//$layout->push('main', array('view'=>'trends'));
//$layout->push('main', array('view'=>'yelp'));






$viewdefs['Accounts']['summer']['layout']['sidebar'] = $layout->getLayout();
