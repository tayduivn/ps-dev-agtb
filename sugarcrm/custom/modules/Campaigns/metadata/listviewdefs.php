<?php
// created: 2010-10-06 18:08:29
$listViewDefs['Campaigns'] = array (
  'NAME' => 
  array (
    'width' => '20',
    'label' => 'LBL_LIST_CAMPAIGN_NAME',
    'link' => true,
    'default' => true,
  ),
  'STATUS' => 
  array (
    'width' => '10',
    'label' => 'LBL_LIST_STATUS',
    'default' => true,
  ),
  'CAMPAIGN_TYPE' => 
  array (
    'width' => '10',
    'label' => 'LBL_LIST_TYPE',
    'default' => true,
  ),
  'END_DATE' => 
  array (
    'width' => '10',
    'label' => 'LBL_LIST_END_DATE',
    'default' => true,
  ),
  'TEAM_NAME' => 
  array (
    'width' => '15',
    'label' => 'LBL_LIST_TEAM',
    'default' => true,
  ),
  'ASSIGNED_USER_NAME' => 
  array (
    'width' => '8',
    'label' => 'LBL_LIST_ASSIGNED_USER',
    'module' => 'Employees',
    'id' => 'ASSIGNED_USER_ID',
    'default' => true,
  ),
  'TRACK_CAMPAIGN' => 
  array (
    'width' => '1',
    'label' => '&nbsp;',
    'link' => true,
    'customCode' => ' <a title="{$TRACK_CAMPAIGN_TITLE}" href="index.php?action=TrackDetailView&module=Campaigns&record={$ID}"><img border="0" src="{$TRACK_CAMPAIGN_IMAGE}"></a> ',
    'default' => true,
    'studio' => false,
    'nowrap' => true,
    'sortable' => false,
  ),
  'LAUNCH_WIZARD' => 
  array (
    'width' => '1',
    'label' => '&nbsp;',
    'link' => true,
    'customCode' => ' <a title="{$LAUNCH_WIZARD_TITLE}" href="index.php?action=WizardHome&module=Campaigns&record={$ID}"><img border="0" src="{$LAUNCH_WIZARD_IMAGE}"></a>  ',
    'default' => true,
    'studio' => false,
    'nowrap' => true,
    'sortable' => false,
  ),
);
?>
