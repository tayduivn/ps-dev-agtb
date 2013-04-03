<?php

$subpanels = array(
    'LBL_LEADS_SUBPANEL_TITLE' => 'leads',
    'LBL_OPPORTUNITIES_SUBPANEL_TITLE' => 'opportunities',
    //TODO: Subpanel needs to support read only list for Campaigns (which is actually campaign log relationship)
    //'LBL_CAMPAIGN_LIST_SUBPANEL_TITLE' => 'campaigns',
    'LBL_CASES_SUBPANEL_TITLE' => 'cases',
    'LBL_BUGS_SUBPANEL_TITLE' => 'bugs',
    'LBL_DIRECT_REPORTS_SUBPANEL_TITLE' => 'direct_reports',
    'LBL_NOTES_SUBPANEL_TITLE' => 'notes',
    'LBL_DOCUMENTS_SUBPANEL_TITLE' => 'documents',
);
$layout = MetaDataManager::getLayout("SubPanelLayout", $subpanels);
$viewdefs['Contacts']['base']['layout']['subpanel'] = $layout->getLayout();
