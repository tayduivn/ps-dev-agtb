<?php
$layout = MetaDataManager::getLayout("SubPanelLayout");
// Eventually change this to the proper syntax when we have subpanel lists.
$layout->push(array("name" => "Notes", "context" => array("link" => "notes")));
$viewdefs['Tasks']['base']['layout']['subpanel'] = $layout->getLayout();
