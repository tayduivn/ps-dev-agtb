<?php

$layout = MetaDataManager::getLayout("FilterPanelLayout");
$layout->push(array("name" => "Related Contacts", "context" => array("link" => "contacts"), "toggles" => array("list")));
$layout->push(array("name" => "Related Opportunities", "context" => array("link" => "opportunities"), "toggles" => array("list")));
$viewdefs['Accounts']['base']['layout']['subpanel'] = $layout->getLayout();