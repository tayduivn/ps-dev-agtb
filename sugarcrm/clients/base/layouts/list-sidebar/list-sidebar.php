<?php

$layout = MetaDataManager::getLayout('GenericLayout');
$layout->push(array('view'=>'news'));
$viewdefs['base']['layout']['list-sidebar'] = $layout->getLayout();
