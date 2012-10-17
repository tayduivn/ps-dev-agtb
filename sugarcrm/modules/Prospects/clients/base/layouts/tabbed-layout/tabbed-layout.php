<?php

$layout = MetaDataManager::getLayout('GenericLayout');
$layout->push(array('view'=>'activitystream', 'label'=>'Activity Stream'));

$viewdefs['Prospects']['base']['layout']['tabbed-layout'] = $layout->getLayout();
