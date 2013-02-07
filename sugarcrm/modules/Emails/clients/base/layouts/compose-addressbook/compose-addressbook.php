<?php
$layout = MetaDataManager::getLayout("SideBarLayout");
$layout->push("main", array("view" => "compose-addressbook-headerpane"));
$layout->push("main", array("view" => "compose-addressbook-recipientscontainer"));
$layout->push("main", array("view" => "compose-addressbook-filter"));
$layout->push("main", array("layout" => "compose-addressbook-list"));
//$layout->push("side", array());
$viewdefs["Emails"]["base"]["layout"]["compose-addressbook"] = $layout->getLayout();
