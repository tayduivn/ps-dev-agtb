<?php

$layout = MetaDataManager::getLayout('GenericLayout', array('type' => 'activitystream'));
$layout->push(array("view" => "activitystream-omnibar"));
$layout->push(array("view" => "activitystream-bottom"));
$viewdefs['base']['layout']['activitystream'] = $layout->getLayout();
