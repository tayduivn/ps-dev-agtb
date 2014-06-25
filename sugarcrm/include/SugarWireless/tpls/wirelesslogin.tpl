{*
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
*}
{if $login_error}<br /><div class="error"><small>{$error_message}</small></div><br />{/if}
<!-- LOGIN FORM -->
<div class="sec">
<form method="post" action="index.php">
	<small>{$LBL_USER_NAME}:</small><br />
	<input type="text" name="user_name" value="" autocorrect="off" autocapitalize="off" /><br/>
	<small>{$LBL_PASSWORD}:</small><br />
	<input type="password" value="" name="user_password" /><br/>
	<input type="submit" value="{$LBL_LOGIN_BUTTON_LABEL}" />
	<input type="hidden" value="Users" name="module" />
	<input type="hidden" value="Authenticate" name="action" />
	<input type="hidden" value="Users" name="return_module" />
	{foreach from=$LOGIN_VARS key=key item=var}
		<input type="hidden" name="{$key}" value="{$var}">
	{/foreach}
</form>
<p>
<a href="index.php?module=Users&action=Login&mobile=0">{$LBL_NORMAL_LOGIN}</a>
</p>
</div>
