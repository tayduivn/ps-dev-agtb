<?php
require_once('clients/summer/SideBarLayout.php');
$layout = new SideBarLayout();
$layout->push('main', array('view'=>'linkedin'));
$layout->push('main', array('view'=>'crunchbase'));
$layout->push('main', array('view'=>'maps'));
$layout->push('main', array('view'=>'news'));
$viewdefs['Accounts']['summer']['layout']['sidebar'] = $layout->getLayout();
