<?php
$module_name = 'DCETemplates';
$detailviewJS=getJSPath('modules/DCETemplates/DetailView.js');
$viewdefs = array (
$module_name =>
array (
  'DetailView' => 
  array (
    'templateMeta' => 
    array (
      'includes'=> array(
            array('file'=>'modules/DCETemplates/DetailView.js'),
        ),    
      'form' => 
      array (
        'buttons' => 
        array (
          0 => 'EDIT',
          1 => 'DUPLICATE',
          2 => 'DELETE',
          array('customCode'=>'{if $bean->aclAccess("edit") && $bean->convert_status!="yes"}<input title="{$APP.LBL_DCETEMPLATE_CONVERT}" class="button" onclick="convertTemplate(\'{$bean->id}\');" type="button" name="DCEConvert" value="{$APP.LBL_DCETEMPLATE_CONVERT}"  id="dceconvert_button" >{/if}'),
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
            'name' => 'template_name',
            'label' => 'LBL_TEMPLATE_NAME',
          ),
        ),
        20 => 
        array (
          0 => 
          array (
            'name' => 'status',
            'label' => 'LBL_STATUS',
          ),
          1 =>
          array (
            'name' => 'zip_name',
            'label' => 'LBL_ZIP_NAME',
          )
        ),
        30 => 
        array (
          0 => array(
          'name' => 'convert_status',
          'label' => 'LBL_CONVERTED_STATUS',            
          ),
          1 => 
          array (
            'name' => 'sugar_edition',
            'label' => 'LBL_SUGAR_EDITION',
          ),
        ),
        40 => 
        array (
          0 => NULL,
          1 => 
          array (
            'name' => 'sugar_version',
            'label' => 'LBL_SUGAR_VERSION',
          ),

        ),
        50 => 
        array (
          0 => NULL,
          1 => 
          array (
            'name' => 'upgrade_acceptable_version',
            'label' => 'LBL_UPGRADE_ACCEPTABLE_VERSION',
          ),

        ),
        60 => 
        array (
          0 =>
          array (
            'name' => 'description',
            'label' => 'LBL_DESCRIPTION',
          ),
          1 => 
          array (
            'name' => 'upgrade_acceptable_edition',
            'label' => 'LBL_UPGRADE_ACCEPTABLE_EDITION',
          ),

        ),

        70 => 
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
        80 => 
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
