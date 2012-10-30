<?php

$layout = MetaDataManager::getLayout('GenericLayout');
$layout->push(array('view' => 'activitystream', 'label' => 'Activity Stream'));
$viewdefs['Accounts']['summer']['layout']['subpanel'] = $layout->getLayout();