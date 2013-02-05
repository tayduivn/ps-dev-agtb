<?php

$layout = MetaDataManager::getLayout("SideBarLayout");
$layout->push("main", array(
    "layout" => array(
        "type" => "drawer",
        "showEvent" => array(
            "dashlet:create:fire",
        ),
    ),
));
$layout->push("main", array("layout" => "list"));
$layout->push("side", array("layout" => "list-sidebar"));
$layout->push("preview", array("layout" => "preview"));
$viewdefs["Home"]["base"]["layout"]["records"] = $layout->getLayout();