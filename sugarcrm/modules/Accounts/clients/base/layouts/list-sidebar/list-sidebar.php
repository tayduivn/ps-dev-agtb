<?php

$layout = MetaDataManager::getLayout('GenericLayout');
$layout->push(array('view'=>'countrychart', 'context'=>array('source'=>'SalesByCountry')));
$layout->push(array('view'=>'activitystream'));
$viewdefs['Accounts']['base']['layout']['list-sidebar'] = $layout->getLayout();
