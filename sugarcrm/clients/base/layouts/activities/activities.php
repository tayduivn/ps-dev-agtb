<?php

$layout = MetaDataManager::getLayout("FilterPanelLayout", array("default" => "activitystream"));
$viewdefs['base']['layout']['activities'] = $layout->getLayout();
