<?php
$module_name = 'fonuae_PBXSettings';
$viewdefs [$module_name] = 
array (
  'EditView' => 
  array (
    'templateMeta' => 
    array (
      'form' => 
      array (
        'buttons' => 
        array (
          0 =>
          array(
          	'customCode' => '<input title="{$APP.LBL_SAVE_BUTTON_TITLE}" accesskey="S" class="button" onclick="this.form.action.value=\'Save\'; ' .
          	'if(check_form(\'EditView\'))'.
			'{literal}{ '.
			'verifyCredentials();return false;' .
			'}else{'.
			'return false;'.
			'}{/literal}" '.
			'name="button" value="{$APP.LBL_SAVE_BUTTON_LABEL}" type="submit"> <div id="ajax_loader" style="display:none; color: orange">Verifying credentials <img src="fonality/include/images/ajax-loader.gif"></div>',
          ),
          1 => 'CANCEL',
        ),
        'footerTpl' =>'modules/fonuae_PBXSettings/tpls/EditViewFooter.tpl'
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
          0 => 
          array (
            'name' => 'username',
            'label' => 'LBL_USERNAME',
          ),
          1 => 
          array (
            'name' => 'password',
            'label' => 'LBL_PASSWORD',
            'customCode' => '<input id="password" type="password" name="password" value="{$fields.password.value}" size="30">' .
            				'<input id="server_id" type="hidden" name="server_id" value="{$fields.server_id.value}">',
          ),
        ),
        1 => 
        array (
          0 => 
          array (
            'name' => 'assigned_user_name',
            'label' => 'LBL_ASSIGNED_TO_NAME',
          ),
          1 => NULL,
        ),
      ),
    ),
  ),
);
?>
