<?php
$layout = MetaDataManager::getLayout('GenericLayout');

$layout->push(array('view'=>'twitter'));
$layout->push(array('view'=>'maps'));
$layout->push(array('view'=>'todo-list'));
$layout->push(array('view'=>'linkedin'));
$layout->push(array('view'=>'facebook'));
$viewdefs['Users']['summer']['layout']['sidebar'] = $layout->getLayout();
