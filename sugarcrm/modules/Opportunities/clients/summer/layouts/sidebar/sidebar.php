<?php

$layout = new GenericLayout();
$layout->push(array('view'=>'todo-list'));
//$layout->push(array('view'=>'attachments'));
//$layout->push(array('view'=>'exchangerates'));
//$layout->push(array('view'=>'currencyconverter'));
$viewdefs['Opportunities']['summer']['layout']['sidebar'] = $layout->getLayout();
