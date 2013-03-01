<?php

$subpanels = array(
    'LBL_CASES_SUBPANEL_TITLE' => 'cases',
    'LBL_CONTACTS_SUBPANEL_TITLE' => 'contacts',
    'LBL_ACCOUNTS_SUBPANEL_TITLE' => 'accounts',
    'Notes' => 'notes',
    'LBL_DOCUMENTS_SUBPANEL_TITLE' => 'documents',
);
$layout = MetaDataManager::getLayout("SubPanelLayout", $subpanels);
$viewdefs['Bugs']['base']['layout']['subpanel'] = $layout->getLayout();
