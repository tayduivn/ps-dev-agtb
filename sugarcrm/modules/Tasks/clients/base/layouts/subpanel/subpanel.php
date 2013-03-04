<?php

$subpanels = array(
    'LBL_HISTORY_SUBPANEL_TITLE' => 'notes',
);

$layout = MetaDataManager::getLayout("SubPanelLayout", $subpanels);
$viewdefs['Tasks']['base']['layout']['subpanel'] = $layout->getLayout();
