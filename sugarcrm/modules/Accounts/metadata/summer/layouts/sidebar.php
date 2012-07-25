<?php
require_once('clients/summer/SideBarLayout.php');
$layout = new SideBarLayout();

$layout->push('main', array('view'=>'maps'));
$layout->push('main', array('view'=>'news'));
$layout->push('main', array('view'=>'linkedin'));
$layout->push('main', array('view'=>'currencyconverter'));
$layout->push('main', array('view'=>'exchangerates'));
$layout->push('main', array('view'=>'trends'));
$layout->push('main', array('view'=>'imagesearch'));


$viewdefs['Accounts']['summer']['layout']['sidebar'] = $layout->getLayout();
