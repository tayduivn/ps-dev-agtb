<?php
$viewdefs['ProspectLists']['base']['layout']['subpanels'] = array (
  'components' =>
  array (
    array (
      'layout' => 'subpanel',
      'label' => 'LBL_PROSPECTS_SUBPANEL_TITLE',
      'context' => array (
        'link' => 'prospects',
      ),
    ),
    array (
      'layout' => 'subpanel',
      'label' => 'LBL_CONTACTS_SUBPANEL_TITLE',
      'context' => array (
        'link' => 'contacts',
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
      'label' => 'LBL_USERS_SUBPANEL_TITLE',
      'context' => array (
        'link' => 'users',
      ),
    ),
    array (
      'layout' => 'subpanel',
      'label' => 'LBL_ACCOUNTS_SUBPANEL_TITLE',
      'override_subpanel_list_view' => 'subpanel-for-prospectlists',
      'context' => array (
        'link' => 'accounts',
      ),
    ),
    array (
      'layout' => 'subpanel',
      'label' => 'LBL_CAMPAIGNS_SUBPANEL_TITLE',
      'context' => array (
        'link' => 'campaigns',
      ),
    ),
  ),
  'type' => 'subpanels',
  'span' => 12,
);
