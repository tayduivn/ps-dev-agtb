<?php

$layout = MetaDataManager::getLayout("FilterPanelLayout", array("notabs" => true));
$viewdefs['base']['layout']['subpanel'] = $layout->getLayout();