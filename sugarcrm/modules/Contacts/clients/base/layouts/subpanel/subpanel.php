<?php

$subpanels = array(
    'LBL_LEADS_SUBPANEL_TITLE' => 'leads',
    'LBL_OPPORTUNITIES_SUBPANEL_TITLE' => 'opportunities',
    'LBL_QUOTES_SUBPANEL_TITLE' => 'quotes',
    'LBL_CAMPAIGN_LIST_SUBPANEL_TITLE' => 'campaign_contacts',
    'LBL_CASES_SUBPANEL_TITLE' => 'cases',
    'LBL_BUGS_SUBPANEL_TITLE' => 'bugs',
    'LBL_DIRECT_REPORTS_SUBPANEL_TITLE' => 'reports_to_link',
    'Notes' => 'notes',
    'LBL_DOCUMENTS_SUBPANEL_TITLE' => 'documents',
);
$layout = MetaDataManager::getLayout("SubPanelLayout", $subpanels);
$viewdefs['Contacts']['base']['layout']['subpanel'] = $layout->getLayout();
