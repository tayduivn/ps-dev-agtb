<?php
$layout = MetaDataManager::getLayout("SubPanelLayout");
// Eventually change this to the proper syntax when we have subpanel lists.
$layout->push(array("name" => "Member Organizations", "context" => array("link" => "members")));
$layout->push(array("name" => "Contacts", "context" => array("link" => "contacts")));
$layout->push(array("name" => "Opportunities", "context" => array("link" => "opportunities")));
$layout->push(array("name" => "Quotes", "context" => array("link" => "quotes")));
$layout->push(array("name" => "Leads", "context" => array("link" => "leads")));
$layout->push(array("name" => "Campaigns", "context" => array("link" => "campaign_accounts")));
$layout->push(array("name" => "Cases", "context" => array("link" => "cases")));
$layout->push(array("name" => "Bugs", "context" => array("link" => "bugs")));
$layout->push(array("name" => "Products", "context" => array("link" => "products")));
$layout->push(array("name" => "Notes", "context" => array("link" => "notes")));
$layout->push(array("name" => "Documents", "context" => array("link" => "documentsw")));
$viewdefs['Accounts']['base']['layout']['subpanel'] = $layout->getLayout();
