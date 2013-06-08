<?php

$layout = MetaDataManager::getLayout('GenericLayout', array('type' => 'preview-activitystream'));
$layout->push(array("view" => "activitystream-bottom"));
$viewdefs['base']['layout']['preview-activitystream'] = $layout->getLayout();
