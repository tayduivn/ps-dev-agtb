<?php

$subpanels = array(
    'LBL_PROSPECTS_SUBPANEL_TITLE' => 'prospects',
    'LBL_CONTACTS_SUBPANEL_TITLE' => 'contacts',
    'LBL_LEADS_SUBPANEL_TITLE' => 'leads',
    'LBL_USERS_SUBPANEL_TITLE' => 'users',
    'LBL_ACCOUNTS_SUBPANEL_TITLE' => 'accounts',
);
$layout = MetaDataManager::getLayout("SubPanelLayout", $subpanels);
$viewdefs['ProspectLists']['base']['layout']['subpanel'] = $layout->getLayout();
