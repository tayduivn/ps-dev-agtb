<?php

$layout = MetaDataManager::getLayout('GenericLayout', array('type' => 'activitystream'));
$layout->push(array("view" => "activitystream-omnibar"));
$viewdefs['base']['layout']['activitystream'] = $layout->getLayout();
