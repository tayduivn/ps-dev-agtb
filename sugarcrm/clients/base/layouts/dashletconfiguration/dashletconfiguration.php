<?php
$layout = MetaDataManager::getLayout("SideBarLayout");
$layout->push("main", array("view" => "dashletconfiguration-headerpane"));
$layout->push("side", array("layout" => "dashlet-sidebar"));
$viewdefs["base"]["layout"]["dashletconfiguration"] = $layout->getLayout();
$viewdefs["base"]["layout"]["dashletconfiguration"]["type"] = "dashletconfiguration";
