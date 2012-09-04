<?php

require_once('clients/summer/SideBarLayout.php');
$layout = new SideBarLayout();
//$layout->push('top', array('view'=>'subnav'));
// $layout->push('main',array('view'=>'agenda'));
$layout->push('main', array('layout'=>'sublist','context'=>array('link'=>'contacts')));
$viewdefs['Meetings']['summer']['layout']['sidebar'] = $layout->getLayout();
