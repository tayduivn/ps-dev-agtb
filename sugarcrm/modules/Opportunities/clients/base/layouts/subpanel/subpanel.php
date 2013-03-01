<?php
$layout = MetaDataManager::getLayout("SubPanelLayout");
// Eventually change this to the proper syntax when we have subpanel lists.
$layout->push(array("name" => "Contacts", "context" => array("link" => "contacts")));
$layout->push(array("name" => "quotes", "context" => array("link" => "quotes")));
$layout->push(array("name" => "Notes", "context" => array("link" => "notes")));
$layout->push(array("name" => "leads", "context" => array("link" => "leads")));
$layout->push(array("name" => "Documents", "context" => array("link" => "documents")));
$viewdefs['Opportunities']['base']['layout']['subpanel'] = $layout->getLayout();
