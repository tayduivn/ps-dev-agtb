<?php
$layout = MetaDataManager::getLayout("SideBarLayout");
$layout->push("main", array("view" => "dashletconfiguration-headerpane"));
$layout->push("side", array("layout" => "sidebar"));
$viewdefs["Home"]["base"]["layout"]["dashletconfiguration"] = $layout->getLayout();
$viewdefs["Home"]["base"]["layout"]["dashletconfiguration"]["type"] = "dashletconfiguration";
