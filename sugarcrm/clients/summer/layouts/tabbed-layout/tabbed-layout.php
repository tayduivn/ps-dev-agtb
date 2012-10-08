<?php

$layout = MetaDataManager::getLayout('GenericLayout');
$layout->push(array('view'=>'activitystream', 'label'=>'Activity Stream'));
$viewdefs['summer']['layout']['tabbed-layout'] = $layout->getLayout();
