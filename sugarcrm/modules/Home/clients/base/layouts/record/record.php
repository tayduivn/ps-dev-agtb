<?php

$layout = MetaDataManager::getLayout("GenericLayout");
$listLayout = MetaDataManager::getLayout("GenericLayout", array("name" => "list"));
$listLayout->push(array("view" => "dashboard-headerpane"));
$listLayout->push(array("layout" => "dashlet-main"));
$layout->push($listLayout->getLayout(true));

$viewdefs['Home']['base']['layout']['record'] = $layout->getLayout();
$viewdefs['Home']['base']['layout']['record']['type'] = 'dashboard';
$viewdefs['Home']['base']['layout']['record']['method'] = 'record';
