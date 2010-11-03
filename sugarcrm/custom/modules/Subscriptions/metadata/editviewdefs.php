<?php
$viewdefs ['Subscriptions'] = 
array (
  'EditView' => 
  array (
    'templateMeta' => 
    array (
      'maxColumns' => '2',
      'widths' => 
      array (
        0 => 
        array (
          'label' => '10',
          'field' => '30',
        ),
        1 => 
        array (
          'label' => '10',
          'field' => '30',
        ),
      ),
      'useTabs' => false,
    ),
    'panels' => 
    array (
      'default' => 
      array (
        0 => 
        array (
          0 => 
          array (
            'name' => 'subscription_id',
            'customCode' => '{if $fields.subscription_id.value != ""}{$fields.subscription_id.value}<input type="hidden" name="subscription_id" id="subscription_id" value="{$fields.subscription_id.value}">{else}<input id="subscription_id" name="subscription_id" size="36" maxlength="36" type="text">{/if}',
            'displayParams' => 
            array (
              'required' => false,
            ),
          ),
          1 => 
          array (
            'name' => 'account_name',
            'label' => 'LBL_ACCOUNT_NAME',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'status',
            'label' => 'LBL_STATUS',
          ),
          1 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO',
          ),
        ),
        2 => 
        array (
          0 => 
          array (
            'name' => 'expiration_date',
            'label' => 'LBL_EXPIRATION_DATE',
          ),
          1 => 
          array (
            'name' => 'team_name',
            'label' => 'LBL_TEAMS',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'term_end_date_c',
            'label' => 'LBL_TERM_END_DATE',
          ),
          1 => '',
        ),
        4 => 
        array (
          0 => 
          array (
            'name' => 'perpetual',
            'label' => 'LBL_PERPETUAL',
          ),
          1 => 
          array (
            'name' => 'enforce_user_limit',
            'label' => 'LBL_ENFORCE_USER_LIMIT',
          ),
        ),
        5 => 
        array (
          0 => 
          array (
            'name' => 'audited',
            'label' => 'LBL_AUDITED',
          ),
          1 => 
          array (
            'name' => 'enforce_portal_users',
            'label' => 'LBL_ENFORCE_PORTAL_USERS',
          ),
        ),
        6 => 
        array (
          0 => 
          array (
            'name' => 'ignore_expiration_date',
            'label' => 'LBL_IGNORE_EXPIRATION_DATE',
          ),
          1 => 
          array (
            'name' => 'portal_users',
            'label' => 'LBL_PORTAL_USERS',
          ),
        ),
      ),
    ),
  ),
);
?>
