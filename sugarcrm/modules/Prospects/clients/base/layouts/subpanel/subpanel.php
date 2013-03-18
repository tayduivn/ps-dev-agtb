<?php

$subpanels = array(
    'LBL_TASKS_SUBPANEL_TITLE'=> 'tasks',
    'LBL_CALLS_SUBPANEL_TITLE'=> 'calls',
    'LBL_MEETINGS_SUBPANEL_TITLE'=> 'meetings',
    'LBL_NOTES_SUBPANEL_TITLE' => 'notes',
);

$layout = MetaDataManager::getLayout("SubPanelLayout", $subpanels);
$viewdefs['Prospects']['base']['layout']['subpanel'] = $layout->getLayout();
