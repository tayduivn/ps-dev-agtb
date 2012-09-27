<?php
require_once('clients/summer/GenericLayout.php');
$layout = new GenericLayout();
$layout->push(array('view'=>'countrychart', 'context'=>array('source'=>'SalesByCountry')));
$layout->push(array('view'=>'activitystream'));
$viewdefs['Accounts']['summer']['layout']['list-sidebar'] = $layout->getLayout();
