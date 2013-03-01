<?php

$subpanels = array(
    'Opportunities' => 'opportunity',
    'LBL_CAMPAIGN_LIST_SUBPANEL_TITLE' => 'campaign_leads',
    'Notes' => 'notes',
);
$layout = MetaDataManager::getLayout("SubPanelLayout", $subpanels);
$viewdefs['Leads']['base']['layout']['subpanel'] = $layout->getLayout();
