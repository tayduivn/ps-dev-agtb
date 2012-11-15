<?php


$layout = MetaDataManager::getLayout('GenericLayout', array('type' => 'list-cluster'));
$layout->push(array('view'=>'list-top'));
$layout->push(array('view'=>'filter'));
$layout->push(array('view'=>'list'));
$layout->push(array('view'=>'list-bottom'));
$viewdefs['base']['layout']['list-cluster'] = $layout->getLayout();
