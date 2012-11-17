<?php

$layout = MetaDataManager::getLayout('GenericLayout', array('type' => 'subpanel', 'default' => 'list'));
$layout->push(array('view' => 'activitystream', 'label' => 'Activity Stream', 'hidden' => 'true'));
$layout->push(array('view' => 'calendar', 'label' => 'Calendar', 'hidden' => 'true'));
$layout->push(array('view' => 'timeline', 'label' => 'Timeline', 'hidden' => 'true'));
$layout->push(array('view' => 'list', 'label' => 'List', 'hidden' => 'true'));
$viewdefs['base']['layout']['subpanel'] = $layout->getLayout();