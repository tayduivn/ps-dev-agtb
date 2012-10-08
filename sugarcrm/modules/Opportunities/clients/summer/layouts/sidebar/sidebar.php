<?php

$layout = MetaDataManager::getLayout('GenericLayout');
$layout->push(array('view' => 'recommended_experts'));
$layout->push(array('view'=>'todo-list'));
$viewdefs['Opportunities']['summer']['layout']['sidebar'] = $layout->getLayout();
