<?php

$layout = MetaDataManager::getLayout('GenericLayout', array('type' => 'preview'));
$layout->push(array("view" => "preview"));
$layout->push(array("view" => "sidebar-stream"));
$viewdefs['summer']['layout']['preview'] = $layout->getLayout();
