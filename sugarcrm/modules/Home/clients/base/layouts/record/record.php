<?php

$layout = MetaDataManager::getLayout("GenericLayout");
$layout->push(array("view" => "dashboard-headerpane"));
$layout->push(array("layout" => "dashlet-main"));

$viewdefs['Home']['base']['layout']['record'] = $layout->getLayout();
$viewdefs['Home']['base']['layout']['record']['type'] = 'dashboard';
$viewdefs['Home']['base']['layout']['record']['method'] = 'record';
