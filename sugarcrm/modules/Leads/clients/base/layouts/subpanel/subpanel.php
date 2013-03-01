<?php
$layout = MetaDataManager::getLayout("SubPanelLayout");
// Eventually change this to the proper syntax when we have subpanel lists.
$layout->push(array("name" => "Opportunities", "context" => array("link" => "opportunity")));
$layout->push(array("name" => "Campaigns", "context" => array("link" => "campaign_leads")));
$layout->push(array("name" => "Notes", "context" => array("link" => "notes")));
$viewdefs['Leads']['base']['layout']['subpanel'] = $layout->getLayout();
