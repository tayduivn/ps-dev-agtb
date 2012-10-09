<?php

$layout = MetaDataManager::getLayout('GenericLayout');
$layout->push(array('view'=>'twitter'));
$layout->push(array('view'=>'maps'));
$layout->push(array('view'=>'influencers'));
$layout->push(array('view'=>'interactions'));
$layout->push(array('view'=>'opportunity_metrics'));
$layout->push(array('view'=>'todo-list'));
$layout->push(array('view'=>'gmail'));
$layout->push(array('view'=>'gdrive'));
$layout->push(array('view'=>'linkedin'));
$layout->push(array('view'=>'gplus'));
$viewdefs['Contacts']['summer']['layout']['sidebar'] = $layout->getLayout();
