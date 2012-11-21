<?php

$layout = MetaDataManager::getLayout("FilterPanelLayout", array("override" => true));
$layout->setTab(array("name" => "List", "view" => "list", "default" => true, "toggles" => array("list")));
$layout->setDefaultTab("List");
$viewdefs['base']['layout']['list'] = $layout->getLayout();