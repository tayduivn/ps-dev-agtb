<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */
$subpanels = array(
    'LBL_CONTACTS_SUBPANEL_TITLE' => 'contact_link',
//    'LBL_LEADS_SUBPANEL_TITLE' => 'leads', // not currently in var defs
    'LBL_DOCUMENTS_SUBPANEL_TITLE' => 'documents',
    'LBL_NOTES_SUBPANEL_TITLE' => 'notes',
);
$layout = MetaDataManager::getLayout("SubPanelLayout", $subpanels);
$viewdefs['RevenueLineItems']['base']['layout']['subpanel'] = $layout->getLayout();
