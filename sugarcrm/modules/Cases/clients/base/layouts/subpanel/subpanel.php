<?php

$subpanels = array(
    'LBL_CONTACTS_SUBPANEL_TITLE' => 'contacts',
    'LBL_BUGS_SUBPANEL_TITLE' => 'bugs',
    'LBL_NOTES_SUBPANEL_TITLE' => 'notes',
    'LBL_DOCUMENTS_SUBPANEL_TITLE' => 'documents',
);
$layout = MetaDataManager::getLayout("SubPanelLayout", $subpanels);
$viewdefs['Cases']['base']['layout']['subpanel'] = $layout->getLayout();
