<?php

$layout = MetaDataManager::getLayout('GenericLayout', array('type' => 'duplicate-list'));
//$layout->push(array('view'=>'list-top'));
$viewdefs['base']['layout']['duplicate-list'] = $layout->getLayout();