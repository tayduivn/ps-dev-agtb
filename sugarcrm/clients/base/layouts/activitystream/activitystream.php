<?php

$layout = MetaDataManager::getLayout('GenericLayout', array('type' => 'activitystream'));
$layout->push(array("view" => "activitystream"));
$viewdefs['base']['layout']['activitystream'] = $layout->getLayout();
