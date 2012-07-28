<?php
require_once('clients/summer/SideBarLayout.php');
$layout = new SideBarLayout();
$layout->push('main', array('view'=>'exchangerates'));
$layout->push('main', array('view'=>'currencyconverter'));
$viewdefs['Opportunities']['summer']['layout']['sidebar'] = $layout->getLayout();
