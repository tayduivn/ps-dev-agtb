<?php

$subpanels = array(
    //TODO: Subpanel needs to support read only list for Campaigns (which is actually campaign log relationship)
    //'LBL_CAMPAIGNS_SUBPANEL_TITLE'=> 'campaigns',
    'LBL_NOTES_SUBPANEL_TITLE' => 'notes',
);

$layout = MetaDataManager::getLayout("SubPanelLayout", $subpanels);
$viewdefs['Prospects']['base']['layout']['subpanel'] = $layout->getLayout();
