<?php
$module_name = 'DCEInstances';
$mod_strings=$GLOBALS['mod_strings'];
global $current_user;
$json = getJSONobj();
$is_admin=$json->encode(is_admin($current_user));
$js_evaluation_duration_list=$json->encode($GLOBALS['app_list_strings']['evaluation_duration_list']);
$js_evaluation_ext_duration_list=$json->encode($GLOBALS['app_list_strings']['evaluation_extended_duration_list']);
$js_production_duration_list=$json->encode($GLOBALS['app_list_strings']['production_duration_list']);
$js_production_ext_duration_list=$json->encode($GLOBALS['app_list_strings']['production_extended_duration_list']);
$editviewJS=getJSPath('modules/DCEInstances/EditView.js');
$viewdefs = array (
$module_name =>
array (
  'EditView' => 
  array (
    'templateMeta' => 
    array (
      'form'=>array(
        'hidden'=>array(
        '<input type="hidden" name="cluster_url" id="cluster_url" value="">',
        '<input type="hidden" name="parent_dceinstance_id" id="parent_dceinstance_id" value="{$fields.parent_dceinstance_id.value}">',
        '<input type="hidden" name="cluster_url_format" id="cluster_url_format" value="">',
        '<input type="hidden" name="parent_dceinstance_name" id="parent_dceinstance_name" value="{$fields.parent_dceinstance_name.value}">',
        '<input type="hidden" name="get_key_user_id" id="get_key_user_id" value="{$fields.get_key_user_id.value}">',
        '<input type="hidden" name="update_key_user_id" id="update_key_user_id" value="{$fields.update_key_user_id.value}">',
        '<input type="hidden" name="current_user_id" id="current_user_id" value="'.$current_user->id.'">',
        '<input type="hidden" name="license_key_status" id="license_key_status" value="{$fields.license_key_status.value}">',
        '<input type="hidden" name="license_field_change" id="license_field_change" value="false">',
        
        '<input type="hidden" name="old_value_license_key" id="old_value_license_key" value="{$fields.license_key.value}" disabled>',
        '<input type="hidden" name="old_value_type" id="old_value_type" value="{$fields.type.value}" disabled>',
        '<input type="hidden" name="old_value_license_start_date" id="old_value_license_start_date" value="{$fields.license_start_date.value}" disabled>',
        '<input type="hidden" name="old_value_license_duration" id="old_value_license_duration" value="{$fields.license_duration.value}" disabled>',
        '<input type="hidden" name="old_value_licensed_users" id="old_value_licensed_users" value="{$fields.licensed_users.value}" disabled>',
       )
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
      'javascript' => <<<EOQ
{literal}
<script type="text/javascript">
var eval_list=$js_evaluation_duration_list;
var eval_ext_list=$js_evaluation_ext_duration_list;
var eval_list_default='{$GLOBALS['app_list_strings']['evaluation_duration_default_key']}';
var eval_ext_list_default='{$GLOBALS['app_list_strings']['evaluation_extended_duration_default_key']}';
var prod_list=$js_production_duration_list;
var prod_ext_list=$js_production_ext_duration_list;
var prod_list_default='{$GLOBALS['app_list_strings']['production_duration_default_key']}';
var prod_ext_list_default='{$GLOBALS['app_list_strings']['production_extended_duration_default_key']}';
var is_admin=$is_admin;
</script>
<script type="text/javascript" language="Javascript" src="$editviewJS"></script>
{/literal}
EOQ
    ),
    'panels' => 
    array (
      'default' => 
      array (
        10 => 
        array (
          0 => 
          array (
            'name' => 'name',
            'label' => 'LBL_NAME',
          ),
          1 => 
          array (
            'name' => 'parent_dceinstance_name',
            'label' => 'LBL_PARENT',
            'type' => 'readonly',
          ),
        ),
        20 => 
        array (
          0 => 
          array (
            'name' => 'account_name',
            'label' => 'LBL_ACCOUNT',
          ),
          1 =>
          array (
            'name' => 'status',
            'label' => 'LBL_STATUS',
          ),
        ),
        30 => 
        array (
          0 =>
          array (
            'name' => 'dcecluster_name',
            'label' => 'LBL_CLUSTER',
            'displayParams'=>array(
                'field_to_name_array' => array(
                    'id' => 'dcecluster_id',
                    'name' => 'dcecluster_name',
                    'url' => 'cluster_url',
                    'url_format' => 'cluster_url_format',
                ),
            ),
          ),
          1 => 
          array (
            'name' => 'type',
            'label' => 'LBL_TYPE',
            'displayParams'=>array('javascript'=>'onchange="DCEEditView.update_license_fields();DCEEditView.update_license_duration();DCEEditView.update_expiration_date();"'),
          ),
        ),
        40 => 
        array (
          0 => 
          array (
            'name' => 'dcetemplate_name',
            'label' => 'LBL_TEMPLATE',
            'displayParams'=>array(
                'field_to_name_array' => array(
                    'id' => 'dcetemplate_id',
                    'name' => 'dcetemplate_name',
                    'sugar_version' => 'sugar_version',
                    'sugar_edition' => 'sugar_edition',
                ), 
            ),
          ),
          1 => 
          array (
            'name' => 'license_start_date',
            'label' => 'LBL_LICENSE_START_DATE',
            'displayParams'=>array(
                'field'=>array(
                    'onchange'=>"DCEEditView.onchange_license_field();DCEEditView.update_expiration_date();",
                ),
            ),
          ),
        ),
        50 => 
        array (
          0 => 
          array (
            'name' => 'sugar_version',
            'label' => 'LBL_SUGAR_VERSION',
            'displayParams'=>array('field'=>array('readonly'=>NULL)),
          ),
          1 => 
          array (
            'name' => 'license_duration',
            'label' => 'LBL_LICENSE_DURATION',
            'customCode' => '<input type="hidden" name="license_duration" id="license_duration" value="{$fields.license_duration.value}"><input type="text" id="duration_disabled" size="11" style="display:none" disabled>{html_options name="license_duration_select" onchange="DCEEditView.onchange_license_field();DCEEditView.update_license_duration();DCEEditView.update_expiration_date();" id="license_duration_select" options=$fields.license_duration.options selected=$fields.license_duration.value}<span id="extend_term">&nbsp;{$MOD.LBL_EXTEND_TERM_BY}:&nbsp;<select name="license_duration_extended" id="license_duration_extended"></select></span>',
          ),
        ),
        60 => 
        array (
          0 =>
          array (
            'name' => 'sugar_edition',
            'label' => 'LBL_SUGAR_EDITION',
            'displayParams'=>array('field'=>array('readonly'=>NULL)),
          ),
          1 => 
          array (
            'name' => 'license_expire_date',
            'type' => 'varchar',
            'displayParams'=>array('field'=>array('readonly'=>NULL), 'size'=>'11'),
          ),
        ),
        70 => 
        array (
          0 =>
          array (
            'name' => 'last_accessed',
            'label' => 'LBL_LAST_ACCESSED',
            'displayParams'=>array('field'=>array('readonly'=>NULL), 'hiddeCalendar'=>'true'),
          ),
          1 => 
          array (
            'name' => 'licensed_users',
            'label' => 'LBL_LICENSED_USERS',
            'displayParams'=>array('size'=>'6', 'field'=>array('onchange'=>"DCEEditView.onchange_license_field()")),
          ),
        ),
        80 => 
        array (
          0 => 
          array (
            'name' => 'description',
            'label' => 'LBL_DESCRIPTION',
          ),
          1 => 
          array (
            'name' => 'license_key',
            'label' => 'LBL_LICENSE_KEY',
            'displayParams'=>array(
                'field'=>array('readonly'=>NULL),
                'buttons'=>array(
                    array(
                        'name'=>'get_key_btn',
                        'id'=>'get_key_btn',
                        'value'=>'{$MOD.LBL_GET_KEY}',
                        'onclick'=>'DCEEditView.getKey(\'get\');'
                    ),
                    array(
                        'name'=>'disable_key_btn',
                        'id'=>'disable_key_btn',
                        'value'=>'{$MOD.LBL_DISABLE_KEY}',
                        'onclick'=>'DCEEditView.getKey(\'disable\');'
                    ),
                ),
                'image'=>array(
                    'id'=>'loading_img',
                    'alt'=>'loading...',
                    'src'=>SugarThemeRegistry::current()->getImageURL('sqsWait.gif'),
                    'style'=>'display:none',
                ),
            ),
          ),
        ),
        90 => 
        array (
          0 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO_NAME',
          ),
          1 => 
          array (
            'name' => 'team_name',
            'displayParams' => 
            array (
              'display' => true,
            ),
            'label' => 'LBL_TEAM',
          ),
        ),
        100 => 
        array (
          0 =>
          array (
            'name' => 'evaluation_duration',
            'label' => 'LBL_LICENSE_DURATION',
            'displayParams'=>array('field'=>array('style'=>'display:none')),
          ),
          1 =>
          array (
            'name' => 'production_duration',
            'label' => 'LBL_LICENSE_DURATION',
            'displayParams'=>array('field'=>array('style'=>'display:none')),
          ),
        ),
      ),
    ),
  ),
)
);
?>
