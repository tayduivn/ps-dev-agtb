<?php
$viewdefs['Accounts']['base']['layout']['subpanels'] = array (
  'components' => array (
    array (
      'layout' => 'subpanel',
      'label' => 'LBL_MEMBER_ORG_SUBPANEL_TITLE',
      'context' => array (
        'link' => 'members',
      ),
    ),
    array (
      'layout' => 'subpanel',
      'label' => 'LBL_CONTACTS_SUBPANEL_TITLE',
      'override_subpanel_list_view' => 'subpanel-for-accounts',
      'context' => array (
        'link' => 'contacts',
      ),
    ),
    array (
      'layout' => 'subpanel',
      'label' => 'LBL_OPPORTUNITIES_SUBPANEL_TITLE',
      'override_subpanel_list_view' => 'subpanel-for-accounts',
      'context' => array (
        'link' => 'opportunities',
      ),
    ),
    array (
      'layout' => 'subpanel',
      'label' => 'LBL_LEADS_SUBPANEL_TITLE',
      'context' => array (
        'link' => 'leads',
      ),
    ),
    array (
      'layout' => 'subpanel',
      'label' => 'LBL_CASES_SUBPANEL_TITLE',
      'override_subpanel_list_view' => 'subpanel-for-accounts',
      'context' => array (
        'link' => 'cases',
      ),
    ),
    array (
      'layout' => 'subpanel',
      'label' => 'LBL_BUGS_SUBPANEL_TITLE',
      'context' => array (
        'link' => 'bugs',
      ),
    ),
    array (
      'layout' => 'subpanel',
      'label' => 'LBL_PRODUCTS_SUBPANEL_TITLE',
      'override_subpanel_list_view' => 'subpanel-for-accounts',
      'context' => array (
        'link' => 'products',
      ),
    ),
    array (
      'layout' => 'subpanel',
      'label' => 'LBL_DOCUMENTS_SUBPANEL_TITLE',
      'context' => array (
        'link' => 'documents',
      ),
    ),
    array (
      'layout' => 'subpanel',
      'label' => 'LBL_QUOTES_SUBPANEL_TITLE',
      'override_subpanel_list_view' => 'subpanel-for-accounts',
      'context' => array (
        'link' => 'quotes',
      ),
    ),
    array (
      'layout' => 'subpanel',
      'label' => 'LBL_CAMPAIGN_LIST_SUBPANEL_TITLE',
      'context' => array (
          'link' => 'campaigns',
      ),
    ),
    array (
      'layout' => 'subpanel',
      'label' => 'LBL_CONTRACTS_SUBPANEL_TITLE',
      'override_subpanel_list_view' => 'subpanel-for-accounts',
      'context' => array (
          'link' => 'contracts',
      ),
    ),
  ),
  'type' => 'subpanels',
  'span' => 12,
);
