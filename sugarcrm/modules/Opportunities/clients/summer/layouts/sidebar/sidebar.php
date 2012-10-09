<?php

$layout = MetaDataManager::getLayout('GenericLayout');
$layout->push(array('view' => 'recommended_experts'));
$layout->push(array('view'=>'influencers'));
$layout->push(array('view'=>'interactions'));
$layout->push(array('view'=>'todo-list'));
$viewdefs['Opportunities']['summer']['layout']['sidebar'] = $layout->getLayout();
