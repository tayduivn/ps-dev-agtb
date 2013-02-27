<?php

$layout = MetaDataManager::getLayout('GenericLayout', array('type' => 'preview'));
$layout->push(array("view" => "preview-header"));
$viewdefs['base']['layout']['dashlet-preview'] = $layout->getLayout();
