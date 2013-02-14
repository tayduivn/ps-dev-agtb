<?php
$layout = MetaDataManager::getLayout("SubPanelLayout");
// Eventually change this to the proper syntax when we have subpanel lists.
$layout->push(array("name" => "Member Organizations", 'label'=>'LBL_MEMBER_ORG_SUBPANEL_TITLE',  "context" => array("link" => "members")));
$layout->push(array("name" => "Contacts", "context" => array("link" => "contacts")));
$layout->push(array("name" => "Opportunities", "context" => array("link" => "opportunities")));
$layout->push(array("name" => "Quotes", "context" => array("link" => "quotes")));
$viewdefs['Accounts']['base']['layout']['subpanel'] = $layout->getLayout();