<?php
$viewdefs['Opportunities']['base']['layout']['subpanels'] = array(
    'components' => array(
        //BEGIN SUGARCRM flav=ent ONLY
        array(
            'layout' => 'subpanel',
            'label' => 'LBL_RLI_SUBPANEL_TITLE',
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
            'context' => array(
                'link' => 'contacts',
            ),
        ),
        array(
            'layout' => 'subpanel',
            'label' => 'LBL_NOTES_SUBPANEL_TITLE',
            'context' => array(
                'link' => 'notes',
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
    ),
    'type' => 'subpanels',
    'span' => 12,
);
