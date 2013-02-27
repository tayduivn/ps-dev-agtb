<?php

$layout = MetaDataManager::getLayout('GenericLayout');
$layout->push(array('layout'=>'dashboard', 'context' => array(
    'forceNew' => true,
    'module' => 'Home',
)));

//$layout->push(array('view'=>'countrychart', 'context'=>array('source'=>'SalesByCountry')));
$viewdefs['Accounts']['base']['layout']['list-sidebar'] = $layout->getLayout();
