<?php
$viewdefs['Opportunities']['base']['layout']['subpanels'] = array(
    'components' => array(
        //BEGIN SUGARCRM flav=ent ONLY
        array(
            'layout' => 'subpanel',
            'label' => 'LBL_RLI_SUBPANEL_TITLE',
            'override_subpanel_list_view' => 'subpanel-for-opportunities',
            'context' => array(
                'link' => 'revenuelineitems',
            ),
        ),
        //END SUGARCRM flav=ent ONLY
        array(
            'layout' => 'subpanel',
            'label' => 'LBL_QUOTE_SUBPANEL_TITLE',
            'context' => array(
                'link' => 'quotes',
            ),
        ),
        array(
            'layout' => 'subpanel',
            'label' => 'LBL_INVITEE',
            'override_subpanel_list_view' => 'subpanel-for-opportunities',
            'context' => array(
                'link' => 'contacts',
            ),
        ),
        array(
            'layout' => 'subpanel',
            'label' => 'LBL_LEADS_SUBPANEL_TITLE',
            'context' => array(
                'link' => 'leads',
            ),
        ),
        array(
            'layout' => 'subpanel',
            'label' => 'LBL_DOCUMENTS_SUBPANEL_TITLE',
            'context' => array(
                'link' => 'documents',
            ),
        ),
        array (
            'layout' => 'subpanel',
            'label' => 'LBL_CONTRACTS_SUBPANEL_TITLE',
            'context' => array (
                'link' => 'contracts',
            ),
        ),
    ),
    'type' => 'subpanels',
    'span' => 12,
);
