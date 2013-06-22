<?php
$viewdefs['Leads']['base']['layout']['subpanels'] = array (
  'components' => array (
    array (
      'layout' => 'subpanel',
      'label' => 'LBL_NOTES_SUBPANEL_TITLE',
      'context' => array (
        'link' => 'notes',
      ),
    ),
    array (
      'layout' => 'subpanel',
      'label' => 'LBL_CAMPAIGN_LIST_SUBPANEL_TITLE',
      'context' => array (
          'link' => 'campaigns',
      ),
    ),
  ),
  'type' => 'subpanels',
  'span' => 12,
);
