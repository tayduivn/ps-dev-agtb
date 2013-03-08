<?php

$layout = MetaDataManager::getLayout('GenericLayout', array('type' => 'filter'));
$layout->push(array("view" => "filter-module-dropdown"));
$layout->push(array("view" => "filter-filter-dropdown"));
$layout->push(array("view" => "filter-quicksearch"));
$viewdefs['base']['layout']['filter'] = $layout->getLayout();
