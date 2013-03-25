<?php

$layout = MetaDataManager::getLayout('GenericLayout');
$layout->push(array("view" => "convert-results"));
$viewdefs['Prospects']['base']['layout']['extra-info'] = $layout->getLayout();
