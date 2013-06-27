<?php
$viewdefs['Contacts']['base']['layout']['subpanels'] = array (
  'components' => array (
    array (
      'layout' => "subpanel",
      'label' => 'LBL_LEADS_SUBPANEL_TITLE',
      'context' => array (
        'link' => 'leads',
      ),
    ),
    array (
      'layout' => "subpanel",
      'label' => 'LBL_OPPORTUNITIES_SUBPANEL_TITLE',
      'context' => array (
        'link' => 'opportunities',
      ),
    ),
    array (
      'layout' => "subpanel",
      'label' => 'LBL_CASES_SUBPANEL_TITLE',
      'context' => array (
        'link' => 'cases',
      ),
    ),
    array (
      'layout' => "subpanel",
      'label' => 'LBL_BUGS_SUBPANEL_TITLE',
      'context' => array (
        'link' => 'bugs',
      ),
    ),
    array (
      'layout' => 'subpanel',
      'label' => 'LBL_DIRECT_REPORTS_SUBPANEL_TITLE',
      'override_subpanel_list_view' => 'subpanel-for-contacts',
      'context' => array (
        'link' => 'direct_reports',
      ),
    ),
    array (
      'layout' => "subpanel",
      'label' => 'LBL_NOTES_SUBPANEL_TITLE',
      'context' => array (
        'link' => 'notes',
      ),
    ),
    array (
      'layout' => "subpanel",
      'label' => 'LBL_DOCUMENTS_SUBPANEL_TITLE',
      'context' => array (
        'link' => 'documents',
      ),
    ),
    array (
      'layout' => 'subpanel',
      'label' => 'LBL_QUOTES_SUBPANEL_TITLE',
      'context' => array (
        'link' => 'quotes',
      ),
    ),
  ),
  'type' => 'subpanels',
  'span' => 12,
);
