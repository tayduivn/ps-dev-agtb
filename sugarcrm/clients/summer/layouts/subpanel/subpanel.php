<?php

$layout = MetaDataManager::getLayout('GenericLayout', array('type' => 'subpanel'));
$layout->push(array('view' => 'activitystream', 'label' => 'Activity Stream'));
$layout->push(array('view' => 'calendar', 'label' => 'Calendar', 'hidden' => 'true'));
$layout->push(array('view' => 'timeline', 'label' => 'Timeline', 'hidden' => 'true'));
$viewdefs['summer']['layout']['subpanel'] = $layout->getLayout();
