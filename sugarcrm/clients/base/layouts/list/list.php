<?php

$layout = MetaDataManager::getLayout("FilterPanelLayout", array("override" => true, "notabs" => true));
$listLayout = MetaDataManager::getLayout("GenericLayout", array("name" => "list"));
$listLayout->push(array("view" => "massupdate"));
$listLayout->push(array("view" => "list"));
$listLayout->push(array('view' => 'list-bottom'));
$layout->push(array("toggles" => $listLayout->getLayout(true)));
$viewdefs['base']['layout']['list'] = $layout->getLayout();
