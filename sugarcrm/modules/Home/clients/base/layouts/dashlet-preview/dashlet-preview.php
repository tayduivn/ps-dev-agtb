<?php

$layout = MetaDataManager::getLayout('GenericLayout', array('type' => 'dashlet-preview'));
$layout->push(array("view" => "preview-header"));
$viewdefs['Home']['base']['layout']['dashlet-preview'] = $layout->getLayout();
