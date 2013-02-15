<?php
$layout = MetaDataManager::getLayout("SideBarLayout");
$layout->push("main", array("view" => "compose-signatures-headerpane"));
$layout->push("main", array("view" => "compose-signatures-list"));
$layout->push("main", array("view" => "compose-signatures-list-bottom"));
$viewdefs["Emails"]["base"]["layout"]["compose-signatures-selection"] = $layout->getLayout();
