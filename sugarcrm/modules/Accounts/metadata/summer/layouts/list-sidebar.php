<?php
require_once('clients/summer/SideBarLayout.php');
$layout = new SideBarLayout();
$layout->push('main', array('view'=>'countrychart', 'context'=>array('source'=>'SalesByCountry')));
$layout->push('main',array('view'=>'activitystream'));
$viewdefs['Accounts']['summer']['layout']['list-sidebar'] = $layout->getLayout();
