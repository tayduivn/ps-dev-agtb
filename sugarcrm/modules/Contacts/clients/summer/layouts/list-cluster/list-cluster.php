<?php
$layout = MetaDataManager::getLayout('GenericLayout');
$layout->push(array('view'=>'list-top'));
$layout->push(array('view'=>'filter'));
$layout->push(array('view'=>'list'));
$layout->push(array('view'=>'list-bottom'));
$viewdefs['Contacts']['summer']['layout']['list-cluster'] = $layout->getLayout();
