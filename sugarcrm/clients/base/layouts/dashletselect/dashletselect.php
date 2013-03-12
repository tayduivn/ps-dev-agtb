<?php
$layout = MetaDataManager::getLayout("SideBarLayout");
$layout->push("main", array("view" => "dashletselect-headerpane"));
$layout->push("main", array("view" => "dashletselect"));
$layout->push("side", array("layout" => "dashlet-sidebar"));
$layout->push('preview', array('layout' => 'dashlet-preview'));
$viewdefs["base"]["layout"]["dashletselect"] = $layout->getLayout();
$viewdefs["base"]["layout"]["dashletselect"]["type"] = "dashletselect";
