<?php

$layout = MetaDataManager::getLayout('GenericLayout');
//$layout->push(array('view'=>'countrychart', 'context'=>array('source'=>'SalesByCountry')));
$layout->push(array('layout' => 'dashboard', 'context' => array(
    'forceNew' => true,
    'module' => 'Home',
)));
$viewdefs['Accounts']['base']['layout']['list-sidebar'] = $layout->getLayout();
