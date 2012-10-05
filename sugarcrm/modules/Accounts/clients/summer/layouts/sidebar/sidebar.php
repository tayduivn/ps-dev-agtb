<?php

$layout = new GenericLayout();
$layout->push(array('view'=>'crunchbase'));
$layout->push(array('view'=>'news'));
$layout->push(array('view'=>'opportunity_metrics'));
$layout->push(array('view'=>'twitter'));
$layout->push(array('view'=>'todo-list'));
$layout->push(array('view'=>'maps'));

$viewdefs['Accounts']['summer']['layout']['sidebar'] = $layout->getLayout();
