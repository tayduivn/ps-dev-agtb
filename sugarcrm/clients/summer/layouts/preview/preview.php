<?php

$layout = MetaDataManager::getLayout('GenericLayout');
$layout->push(array("view" => "preview"));
$layout->push(array("view" => "preview-stream"));
$viewdefs['summer']['layout']['preview'] = $layout->getLayout();
