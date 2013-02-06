<?php
$layout = MetaDataManager::getLayout("GenericLayout", array("type" => "subpanel"));
// Eventually change this to the proper syntax when we have subpanel lists.
$layout->push(array("name" => "Member Organizations", "context" => array("link" => "members")));
$layout->push(array("name" => "Contacts", "context" => array("link" => "contacts")));
$layout->push(array("name" => "Opportunities", "context" => array("link" => "opportunities")));
$layout->push(array("name" => "Quotes", "context" => array("link" => "quotes")));
$viewdefs['Accounts']['base']['layout']['subpanel'] = $layout->getLayout();