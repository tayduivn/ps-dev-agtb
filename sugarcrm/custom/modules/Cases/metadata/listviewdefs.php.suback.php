<?php
// created: 2006-09-15 17:52:25
$listViewDefs['Cases'] = array (
  'CASE_NUMBER' => 
  array (
    'width' => '5',
    'label' => 'LBL_LIST_NUMBER',
    'default' => true,
  ),
  'NAME' => 
  array (
    'width' => '35',
    'label' => 'LBL_LIST_SUBJECT',
    'link' => true,
    'default' => true,
  ),
  'ACCOUNT_NAME' => 
  array (
    'width' => '25',
    'label' => 'LBL_LIST_ACCOUNT_NAME',
    'module' => 'Accounts',
    'id' => 'ACCOUNT_ID',
    'link' => true,
    'default' => true,
    'ACLTag' => 'ACCOUNT',
    'related_fields' => 
    array (
      0 => 'account_id',
    ),
  ),
  'PRIORITY_LEVEL' => 
  array (
    'width' => 10,
    'label' => 'LBL_PRIORITY_LEVEL',
    'default' => true,
  ),
  'STATUS' => 
  array (
    'width' => '8',
    'label' => 'LBL_LIST_STATUS',
    'default' => true,
  ),
  'SUPPORT_SERVICE_LEVEL_C' => 
  array (
    'width' => 10,
    'label' => 'Support_Service_Level_c',
    'default' => true,
  ),
  'CASE_SCORE' =>
  array (
    'width' => '10',
    'label' => 'LBL_CASE_SCORE',
    'customCode' => '<a href="index.php?module=Cases&action=CaseScoreDetails&case_id={$ID}&to_pdf=1" onclick="window.open(this.href,\'window\',\'width=950,height=400,resizable,menubar\'); return false;">{$CASE_SCORE}</a>',
    'default' => false,
  ),
  'TEAM_NAME' => 
  array (
    'width' => '8',
    'label' => 'LBL_LIST_TEAM',
    'default' => true,
  ),
  'ASSIGNED_USER_NAME' => 
  array (
    'width' => '5',
    'label' => 'LBL_LIST_ASSIGNED_USER',
    'default' => true,
  ),
);
?>
