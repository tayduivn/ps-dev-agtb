<?php
$viewdefs ['Subscriptions'] = 
array (
  'DetailView' => 
  array (
    'templateMeta' => 
    array (
      'form' => 
      array (
        'buttons' => 
        array (
          0 => 'EDIT',
          1 => 'DELETE',
        ),
      ),
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
            'label' => 'LBL_SUBSCRIPTION_ID',
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
            'name' => 'date_entered',
            'label' => 'LBL_DATE_ENTERED',
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
            'name' => 'date_modified',
            'label' => 'LBL_DATE_MODIFIED',
            'customCode' => '{$fields.date_modified.value} by <a href="index.php?module=Employees&action=DetailView&record={$fields.modified_user_id.value}" class="tabDetailViewDFLink">{$fields.modified_user_name.value}</a>',
          ),
        ),
        3 => 
        array (
          0 => 
          array (
            'name' => 'term_end_date_c',
            'label' => 'LBL_TERM_END_DATE',
          ),
          1 => 
          array (
            'name' => 'team_name',
            'label' => 'LBL_TEAMS',
          ),
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
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO',
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
            'name' => 'enforce_user_limit',
            'label' => 'LBL_ENFORCE_USER_LIMIT',
          ),
        ),
        6 => 
        array (
          0 => 
          array (
            'name' => 'debug',
            'label' => 'LBL_DEBUG',
          ),
          1 => 
          array (
            'name' => 'enforce_portal_users',
            'label' => 'LBL_ENFORCE_PORTAL_USERS',
          ),
        ),
        7 => 
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
