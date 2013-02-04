<?php

$layout = MetaDataManager::getLayout("GenericLayout");
$listLayout = MetaDataManager::getLayout("GenericLayout", array("name" => "list"));
$listLayout->push(array("layout" => array(
    'type' => 'drawer',
    'showEvent' => array(
        "drawer:selection:fire",
    )
)));
$listLayout->push(array("view" => "massupdate"));
$listLayout->push(array("view" => "list"));
$listLayout->push(array('view' => 'list-bottom'));
$layout->push($listLayout->getLayout(true));
$viewdefs['base']['layout']['list'] = $layout->getLayout();
