<?php

$subpanels = array(
    'LBL_OPPORTUNITIES_SUBPANEL_TITLE' => 'opportunity',
    'LBL_CAMPAIGN_LIST_SUBPANEL_TITLE' => 'campaign_leads',
    'LBL_NOTES_SUBPANEL_TITLE' => 'notes',
);
$layout = MetaDataManager::getLayout("SubPanelLayout", $subpanels);
$viewdefs['Leads']['base']['layout']['subpanel'] = $layout->getLayout();
