<?php
$layout = MetaDataManager::getLayout("GenericLayout");
$layout->push(array("view" => "dashboard-headerpane"));
$viewdefs['Home']['base']['layout']['list'] = $layout->getLayout();
$viewdefs['Home']['base']['layout']['list']['type'] = 'dashboard';
