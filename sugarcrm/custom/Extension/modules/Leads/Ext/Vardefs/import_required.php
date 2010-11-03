<?php
/* Dee customization 12.02.2008 - set importable = required for required fields in leads module while importing */
/*$dictionary['Lead']['fields']['last_name']['importable']  = 'required';
$dictionary['Lead']['fields']['campaign_name']['importable']  = 'required';
$dictionary['Lead']['fields']['account_name']['importable']  = 'required';
$dictionary['Lead']['fields']['primary_address_country']['importable']  = 'required';
*/
$dictionary['Lead']['fields']['assigned_user_id'] = array (
      'name' => 'assigned_user_id',
      'rname' => 'user_name',
      'id_name' => 'assigned_user_id',
      'vname' => 'LBL_ASSIGNED_TO',
      'group' => 'assigned_user_name',
      'type' => 'relate',
      'table' => 'users',
      'module' => 'Users',
      'reportable' => true,
      'isnull' => 'false',
      'dbType' => 'id',
      'audited' => true,
      'comment' => 'User assigned to this record',
      'duplicate_merge' => 'disabled',
);

$dictionary['Lead']['fields']['assigned_user_name'] = array (
      'name' => 'assigned_user_name',
      'vname' => 'LBL_ASSIGNED_TO_NAME',
      'rname' => 'user_name',
      'type' => 'relate',
      'reportable' => false,
      'source' => 'non-db',
      'table' => 'users',
      'id_name' => 'assigned_user_id',
      'module' => 'Users',
      'duplicate_merge' => 'disabled',
      'importable' => 'required',
);
?>
