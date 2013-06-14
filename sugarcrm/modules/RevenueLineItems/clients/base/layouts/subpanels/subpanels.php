<?php
$viewdefs['RevenueLineItems']['base']['layout']['subpanels'] = array (
  'components' => array (
    array (
      'layout' => 'subpanel',
      'label' => 'LBL_CONTACTS_SUBPANEL_TITLE',
      'context' => array (
        'link' => 'contact_link',
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
      'label' => 'LBL_NOTES_SUBPANEL_TITLE',
      'context' => array (
        'link' => 'notes',
      ),
    ),
  ),
  'type' => 'subpanels',
  'span' => 12,
);
