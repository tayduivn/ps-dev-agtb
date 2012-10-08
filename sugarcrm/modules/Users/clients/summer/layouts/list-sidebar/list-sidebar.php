<?php

$layout = MetaDataManager::getLayout('GenericLayout');
$layout->push(array('view'=>'activitystream'));
$viewdefs['Users']['summer']['layout']['list-sidebar'] = $layout->getLayout();
