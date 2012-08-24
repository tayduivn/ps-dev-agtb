<?php
require_once('clients/summer/SideBarLayout.php');
$layout = new SideBarLayout();
//$layout->push('main', array('layout'=>'sublist','context'=>array('link'=>'contacts')));
$layout->push('main', array('view'=>'attachments'));
$layout->push('main', array('view'=>'exchangerates'));
//$layout->push('main', array('view'=>'currencyconverter'));
$viewdefs['Opportunities']['summer']['layout']['sidebar'] = $layout->getLayout();
