<?php

$subpanels = array(
//BEGIN SUGARCRM flav=ent ONLY
    'LBL_PRODUCTS_SUBPANEL_TITLE' => 'products',
//END SUGARCRM flav=ent ONLY
    'LBL_INVITEE' => 'contacts',
    'LBL_QUOTES_SUBPANEL_TITLE' => 'quotes',
    'LBL_NOTES_SUBPANEL_TITLE' => 'notes',
    'LBL_LEADS_SUBPANEL_TITLE' => 'leads',
    'LBL_DOCUMENTS_SUBPANEL_TITLE' => 'documents',
);
$layout = MetaDataManager::getLayout("SubPanelLayout", $subpanels);
$viewdefs['Opportunities']['base']['layout']['subpanel'] = $layout->getLayout();
