<?php
$module_name = 'DCETemplates';
$editviewJS=getJSPath('modules/DCETemplates/EditView.js');
$viewdefs = array (
$module_name =>
array (
  'EditView' => 
  array (
    'templateMeta' => 
    array (
      'form' => array(
        'enctype'=>'multipart/form-data',
        'hidden'=>array(
        '<input type="hidden" name="upgrade_acceptable_edition" id="upgrade_acceptable_edition" value="{$fields.upgrade_acceptable_edition.value}">',
        '<input type="hidden" name="uploadTmpDir" id="uploadTmpDir">',
        '<input type="hidden" name="template_file" id="template_file">')
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
{ext_includes}
{literal}
<div id='upload_panel' style="display:none">
    <form id="upload_form" name="upload_form" method="POST" action='index.php' enctype="multipart/form-data">
        <input type="file" id="my_file" name="file_1" size="20" onchange="uploadCheck()"/>
        <!--not_in_theme!--><img id="loading_img" alt="{$mod_strings['LBL_LOADING']}" src="{/literal}{sugar_getimagepath file='sqsWait.gif'}{literal}" style="display:none">
    </form> 
</div>
<script type="text/javascript" language="Javascript" src="$jsonJS"></script>
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
          array(
            'name' => 'template_name',
            'label' => 'LBL_TEMPLATE_LOCATION',
            'customCode' => '<div id="container_upload">
            </div><input id="template_name" type="text" readonly="" maxlength="255" size="30" name="template_name" style="display:none" value="{$fields.template_name.value}"/>',
            
          )
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
            'name' => 'sugar_version',
            'label' => 'LBL_SUGAR_VERSION',
            'displayParams'=>array('field'=>array('readonly'=>NULL)),
          ),
        ),
        24 => 
        array (
          0 => NULL,
          1 => 
          array (
            'name' => 'sugar_edition',
            'label' => 'LBL_SUGAR_EDITION',
            'displayParams'=>array('field'=>array('readonly'=>NULL)),
          ),
        ),
        27 => 
        array (
          0 => NULL,
          1 => 
          array (
            'name' => 'upgrade_acceptable_version',
            'label' => 'LBL_UPGRADE_ACCEPTABLE_VERSION',
            'displayParams'=>array(
                'image'=>array(
                    'border'=>"0", 
                    'onclick'=>"return SUGAR.util.showHelpTips(this,'".translate('LBL_HELP_ACC_VERSION')."');",
                    'src'=> SugarThemeRegistry::current()->getImageURL("help.gif"),
                  ),
             ),
           ),
        ),
        30 => 
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
            'customCode' => '
            <input class="checkbox" type="checkbox" name="ce_ckbox" id="ce_ckbox" value="CE" onchange="update_acc_edition(false)">  {$MOD.LBL_CE}<BR>
            <input class="checkbox" type="checkbox" name="pro_ckbox" id="pro_ckbox" value="PRO" onchange="update_acc_edition(false)">  {$MOD.LBL_PRO}',
          ),
        ),
        40 => 
        array (
          0 => 
          array(
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
      ),
    ),
  ),
)
);
?>
