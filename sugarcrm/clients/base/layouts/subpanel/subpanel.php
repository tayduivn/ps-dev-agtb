<?php

$layout = MetaDataManager::getLayout('GenericLayout', array('type' => 'subpanel'));
$layout->push(array('view' => 'activitystream', 'label' => 'Activity Stream', 'hidden' => 'true'));
$layout->push(array('view' => 'calendar', 'label' => 'Calendar', 'hidden' => 'true'));
$layout->push(array('view' => 'timeline', 'label' => 'Timeline', 'hidden' => 'true'));
$layout->push(array('view' => 'list', 'label' => 'List'));
$viewdefs['base']['layout']['subpanel'] = $layout->getLayout();
