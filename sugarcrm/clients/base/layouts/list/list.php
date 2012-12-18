<?php

$layout = MetaDataManager::getLayout("FilterPanelLayout", array("override" => true, "notabs" => true));
$layout->push(array("toggles" => array("list")));
$viewdefs['base']['layout']['list'] = $layout->getLayout();