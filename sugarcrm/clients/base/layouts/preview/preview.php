<?php

$layout = MetaDataManager::getLayout('GenericLayout', array('type' => 'preview'));
$layout->push(array("view" => "preview-header"));
$layout->push(array("view" => "preview"));
$viewdefs['base']['layout']['preview'] = $layout->getLayout();
