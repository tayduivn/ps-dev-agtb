<?php
$layout = MetaDataManager::getLayout("GenericLayout", array("type" => "compose-addressbook-list"));
$layout->push(array("view" => "compose-addressbook-list"));
$layout->push(array("view" => "compose-addressbook-list-bottom"));
$viewdefs["Emails"]["base"]["layout"]["compose-addressbook-list"] = $layout->getLayout();
