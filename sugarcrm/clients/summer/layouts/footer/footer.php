<?php

$layout = MetaDataManager::getLayout("GenericLayout");
$layout->push(array("view" => "instance-picker"));
$layout->push(array("view" => "reminders"));
$layout->push(array("view" => "tour-action"));
$layout->push(array("view" => "footer-actions"));

$viewdefs['summer']['layout']['footer'] = $layout->getLayout();