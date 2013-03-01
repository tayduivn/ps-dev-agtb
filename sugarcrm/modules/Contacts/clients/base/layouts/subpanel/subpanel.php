<?php
$layout = MetaDataManager::getLayout("SubPanelLayout");
// Eventually change this to the proper syntax when we have subpanel lists.
$layout->push(array("name" => "Leads", "context" => array("link" => "leads")));
$layout->push(array("name" => "Opportunities", "context" => array("link" => "opportunities")));
$layout->push(array("name" => "Quotes", "context" => array("link" => "quotes")));
$layout->push(array("name" => "Campaigns", "context" => array("link" => "campaign_contacts")));
$layout->push(array("name" => "Cases", "context" => array("link" => "cases")));
$layout->push(array("name" => "Bugs", "context" => array("link" => "bugs")));
$layout->push(array("name" => "Direct Reports", "context" => array("link" => "reports_to_link")));
$layout->push(array("name" => "Notes", "context" => array("link" => "notes")));
$layout->push(array("name" => "Documents", "context" => array("link" => "documents")));
$viewdefs['Contacts']['base']['layout']['subpanel'] = $layout->getLayout();
