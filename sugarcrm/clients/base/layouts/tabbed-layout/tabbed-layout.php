<?php

$layout = MetaDataManager::getLayout('GenericLayout');
$layout->push(array('view'=>'activitystream', 'label'=>'Activity Stream'));
$viewdefs['base']['layout']['tabbed-layout'] = $layout->getLayout();
