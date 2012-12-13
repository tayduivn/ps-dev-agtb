<?php

$layout = MetaDataManager::getLayout("FilterPanelLayout");
$layout->push(array("name" => "Member Organizations", "context" => array("link" => "members"), "toggles" => array("list")));
$layout->push(array("name" => "Contacts", "context" => array("link" => "contacts"), "toggles" => array("list")));
$layout->push(array("name" => "Opportunities", "context" => array("link" => "opportunities"), "toggles" => array("list")));
$layout->push(array("name" => "Quotes", "context" => array("link" => "quotes"), "toggles" => array("list")));
$viewdefs['Accounts']['base']['layout']['subpanel'] = $layout->getLayout();