<?php

$layout = MetaDataManager::getLayout('GenericLayout');
$layout->push(array("view" => "convert-results"));
$viewdefs['Leads']['base']['layout']['extra-info'] = $layout->getLayout();
