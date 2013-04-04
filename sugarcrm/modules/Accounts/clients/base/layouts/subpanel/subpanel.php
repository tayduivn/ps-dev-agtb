<?php

$subpanels = array(
    'LBL_MEMBER_ORG_SUBPANEL_TITLE' => 'members',
    'LBL_CONTACTS_SUBPANEL_TITLE' => 'contacts',
    'LBL_OPPORTUNITIES_SUBPANEL_TITLE' => 'opportunities',
    'LBL_LEADS_SUBPANEL_TITLE' => 'leads',
    //TODO: Subpanel needs to support read only list for Campaigns (which is actually campaign log relationship)
    //'LBL_CAMPAIGNS' => 'campaigns',
    'LBL_CASES_SUBPANEL_TITLE' => 'cases',
    'LBL_BUGS_SUBPANEL_TITLE' => 'bugs',
    'LBL_PRODUCTS_SUBPANEL_TITLE' => 'products',
    'LBL_NOTES_SUBPANEL_TITLE' => 'notes',
    'LBL_DOCUMENTS_SUBPANEL_TITLE' => 'documents',
);
$layout = MetaDataManager::getLayout("SubPanelLayout", $subpanels);
$viewdefs['Accounts']['base']['layout']['subpanel'] = $layout->getLayout();
