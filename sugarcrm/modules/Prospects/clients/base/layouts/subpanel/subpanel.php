<?php

$subpanels = array(
    'Tasks'=> 'tasks',
    'Calls'=> 'calls',
    'Meetings'=> 'meetings',
    'Notes' => 'notes',
);

$layout = MetaDataManager::getLayout("SubPanelLayout", $subpanels);
$viewdefs['Prospects']['base']['layout']['subpanel'] = $layout->getLayout();