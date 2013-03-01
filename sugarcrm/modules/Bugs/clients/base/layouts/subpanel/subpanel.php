<?php
$layout = MetaDataManager::getLayout("SubPanelLayout");
// Eventually change this to the proper syntax when we have subpanel lists.
$layout->push(array("name" => "Cases", "context" => array("link" => "cases")));
$layout->push(array("name" => "Contacts", "context" => array("link" => "contacts")));
$layout->push(array("name" => "Accounts", "context" => array("link" => "accounts")));
$layout->push(array("name" => "Notes", "context" => array("link" => "notes")));
$layout->push(array("name" => "Documents", "context" => array("link" => "documents")));
$viewdefs['Bugs']['base']['layout']['subpanel'] = $layout->getLayout();
