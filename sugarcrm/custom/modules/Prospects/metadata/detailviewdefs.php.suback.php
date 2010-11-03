<?php
$viewdefs ['Prospects'] = 
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
          1 => 'DUPLICATE',
          2 => 'DELETE',
          3 => 
          array (
            'customCode' => '<input title="{$MOD.LBL_CONVERT_BUTTON_TITLE}" accessKey="{$MOD.LBL_CONVERT_BUTTON_KEY}" class="button" onclick="this.form.return_module.value=\'Prospects\'; this.form.return_action.value=\'DetailView\'; this.form.return_id.value=\'{$fields.id.value}\';this.form.module.value=\'LeadContacts\';this.form.action.value=\'EditView\';" type="submit" name="CONVERT_LEAD_BTN" value="{$MOD.LBL_CONVERT_BUTTON_LABEL}"/>',
          ),
          4 => 
          array (
            'customCode' => '<input title="{$APP.LBL_MANAGE_SUBSCRIPTIONS}" class="button" onclick="this.form.return_module.value=\'Prospects\'; this.form.return_action.value=\'DetailView\'; this.form.return_id.value=\'{$fields.id.value}\'; this.form.action.value=\'Subscriptions\'; this.form.module.value=\'Campaigns\';" type="submit" name="Manage Subscriptions" value="{$APP.LBL_MANAGE_SUBSCRIPTIONS}"/>',
          ),
        ),
        'hidden' => 
        array (
          0 => '<input type="hidden" name="prospect_id" value="{$fields.id.value}">',
        ),
        'headerTpl' => 'modules/Prospects/tpls/DetailViewHeader.tpl',
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
    ),
    'panels' => 
    array (
      'default' => 
      array (
        0 => 
        array (
          0 => 'full_name',
          1 => 
          array (
            'name' => 'phone_work',
            'label' => 'LBL_OFFICE_PHONE',
            'customCode' => '{fonality_phone value=$fields.phone_work.value this_module=Prospects this_id=$fields.id.value}',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => '',
            'displayParams' => 
            array (
            ),
          ),
          1 => 
          array (
            'name' => 'phone_mobile',
            'label' => 'LBL_MOBILE_PHONE',
            'customCode' => '{fonality_phone value=$fields.phone_mobile.value this_module=Prospects this_id=$fields.id.value}',
          ),
        ),
        2 => 
        array (
          0 => '',
          1 => 
          array (
            'name' => 'phone_home',
            'label' => 'LBL_HOME_PHONE',
            'customCode' => '{fonality_phone value=$fields.phone_home.value this_module=Prospects this_id=$fields.id.value}',
          ),
        ),
        3 => 
        array (
          0 => 'title',
          1 => 
          array (
            'name' => 'phone_fax',
            'label' => 'LBL_FAX_PHONE',
            'customCode' => '{fonality_phone value=$fields.phone_fax.value this_module=Prospects this_id=$fields.id.value}',
          ),
        ),
        4 => 
        array (
          0 => 'department',
          1 => 'email1',
        ),
        5 => 
        array (
          0 => 'birthdate',
          1 => '',
        ),
        6 => 
        array (
          0 => 'account_name',
          1 => 'assistant',
        ),
        7 => 
        array (
          0 => '',
          1 => 
          array (
            'name' => 'assistant_phone',
            'label' => 'LBL_ASSISTANT_PHONE',
            'customCode' => '{fonality_phone value=$fields.assistant_phone.value this_module=Prospects this_id=$fields.id.value}',
          ),
        ),
        8 => 
        array (
          0 => 'do_not_call',
          1 => 'email_opt_out',
        ),
        9 => 
        array (
          0 => 'team_name',
          1 => 
          array (
            'name' => 'modified_by_name',
            'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}&nbsp;',
            'label' => 'LBL_DATE_MODIFIED',
          ),
        ),
        10 => 
        array (
          0 => 'assigned_user_name',
          1 => 
          array (
            'name' => 'created_by_name',
            'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}&nbsp;',
            'label' => 'LBL_DATE_ENTERED',
          ),
        ),
        11 => 
        array (
          0 => 
          array (
            'name' => 'primary_address_street',
            'label' => 'LBL_PRIMARY_ADDRESS',
            'type' => 'address',
            'displayParams' => 
            array (
              'key' => 'primary',
            ),
          ),
          1 => 
          array (
            'name' => 'alt_address_street',
            'label' => 'LBL_ALTERNATE_ADDRESS',
            'type' => 'address',
            'displayParams' => 
            array (
              'key' => 'alt',
            ),
          ),
        ),
        12 => 
        array (
          0 => 'description',
        ),
      ),
    ),
  ),
);
?>
