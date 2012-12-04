<?php

$layout = MetaDataManager::getLayout("FilterPanelLayout", array("override" => true));
$layout->push(array("toggles" => array("list")));
$viewdefs['base']['layout']['list'] = $layout->getLayout();