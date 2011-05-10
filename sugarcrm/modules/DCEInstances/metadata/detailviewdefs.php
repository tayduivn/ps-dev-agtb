<?php
$module_name = 'DCEInstances';
$viewdefs = array (
$module_name =>
array (
  'DetailView' => 
  array (
    'templateMeta' => 
    array (
      'includes'=> array(
            array('file'=>'modules/DCEInstances/DetailView.js'),
        ),
      'form' => 
      array (
        'hidden' => array('<input type="hidden" name="actionType" id="actionType" value="">'),
        'buttons' => 
        array (
          0 => 'EDIT',
          1 => 'DUPLICATE',
          array('customCode'=>'{if $bean->aclAccess("delete")}<input title="{$APP.LBL_DCEDELETE_LABEL}" class="button" onclick="onClickDelete();" type="button" name="DCEDelete" value="{$APP.LBL_DCEDELETE_LABEL}"  id="dcedelete_button" >{/if}'),
          array('customCode'=>'{if $bean->aclAccess("deploy")}<input title="{$APP.LBL_DCEDEPLOY_LABEL}" class="button" onclick="onClickDeploy();" type="button" name="DCEDeploy" value="{$APP.LBL_DCEDEPLOY_LABEL}"  id="dcedeploy_button" style="display:none">{/if}'),
          array('customCode'=>'{if $bean->aclAccess("upgrade")}<input title="{$APP.LBL_DCEUPGRADE_LABEL}" class="button" onclick="onClickUpgrade();" type="submit" name="DCEUpgrade" value="{$APP.LBL_DCEUPGRADE_LABEL}"  id="dceupgrade_button" style="display:none">{/if}'),
          array('customCode'=>'{if $bean->aclAccess("convert")}<input title="{$APP.LBL_DCECONVERTINSTANCE_BUTTON}" class="button" onclick="onClickInitSubmit(\'convert\');" type="button" name="DCEConvertInstance" value="{$APP.LBL_DCECONVERTINSTANCE_BUTTON}"  id="dceconvertinstance_button" style="display:none">{/if}'),
          array('customCode'=>'{if $bean->aclAccess("archive")}<input title="{$APP.LBL_DCEARCHIVE_BUTTON}" class="button" onclick="onClickInitSubmit(\'archive\');" type="button" name="DCEArchive" value="{$APP.LBL_DCEARCHIVE_BUTTON}"  id="dcearchive_button" style="display:none">{/if}'),
          array('customCode'=>'{if $bean->aclAccess("recover")}<input title="{$APP.LBL_DCERECOVER_BUTTON}" class="button" onclick="onClickInitSubmit(\'recover\');" type="button" name="DCERecover" value="{$APP.LBL_DCERECOVER_BUTTON}" id="dcerecover_button" style="display:none">{/if} '),
          array('customCode'=>'{if $bean->aclAccess("support_user")}<input title="{$APP.LBL_DCESUPPORTUSER_ENABLE_BUTTON}" class="button" onclick="onClickInitSubmit(\'toggle_on\');" type="button" name="DCESupportUser" value="{if $bean->support_user}{$APP.LBL_DCESUPPORTUSER_DISABLE_BUTTON}{else}{$APP.LBL_DCESUPPORTUSER_ENABLE_BUTTON}{/if}" id="dcesupportuser_button" style="display:none">{/if} '),
          
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
    ),
    'panels' => 
    array (
      '' => 
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
        25 => 
        array (
          0 => 
          array (
            'name' => 'dcecluster_name',
            'label' => 'LBL_CLUSTER',
          ),
          1 =>
          array (
            'name' => 'type',
            'label' => 'LBL_TYPE',
          ),
        ),
        30 => 
        array (
          0 => 
          array (
            'name' => 'dcetemplate_name',
            'label' => 'LBL_TEMPLATE',
          ),
          1 => 
          array (
            'name' => 'license_start_date',
            'label' => 'LBL_LICENSE_START_DATE',
          ),
        ),
        40 => 
        array (
          0 => 
          array (
            'name' => 'sugar_version',
            'label' => 'LBL_SUGAR_VERSION',
          ),
          1 => 
          array (
            'name' => 'license_duration',
            'label' => 'LBL_LICENSE_DURATION',
          ),
        ),
        50 => 
        array (
          0 => 
          array (
            'name' => 'sugar_edition',
            'label' => 'LBL_SUGAR_EDITION',
          ),
          1 => 
          array (
            'name' => 'license_expire_date',
            'label' => 'LBL_LICENSE_EXPIRE_DATE',
          ),
        ),
        60 => 
        array (
          0 => 
          array (
            'name' => 'url',
            'label' => 'LBL_URL',
          ),
          1 => 
          array (
            'name' => 'licensed_users',
            'label' => 'LBL_LICENSED_USERS',
          ),
        ),
        70 => 
        array (
          0 => 
          array (
            'name' => 'last_accessed',
            'label' => 'LBL_LAST_ACCESSED',
          ),
          1 => 
          array (
            'name' => 'license_key',
            'label' => 'LBL_LICENSE_KEY',
          ),
        ),
        80 => 
        array (
          0 => 
          array (
            'name' => 'description',
            'label' => 'LBL_DESCRIPTION',
          ),
        ),
        90 => 
        array (
        
          0 => 
          array (
            'name' => 'date_entered',
            'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}',
            'label' => 'LBL_DATE_ENTERED',
          ),
          1 => 
          array (
            'name' => 'date_modified',
            'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}',
            'label' => 'LBL_DATE_MODIFIED',
          ),
        ),
        100 => 
        array (
          0 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO_NAME',
          ),
          1 => 
          array (
            'name' => 'team_name',
            'label' => 'LBL_TEAM',
          ),
        ),
      ),
    ),
  ),
)
);
?>
