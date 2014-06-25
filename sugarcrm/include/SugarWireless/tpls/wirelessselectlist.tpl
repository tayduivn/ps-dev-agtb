
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
<br />
{sugar_translate label='LBL_SELECT_MODULE' module=''}<br />
<form method="post" action="index.php">
	<select name="module">
		{foreach from=$WL_MODULE_LIST item=VALUE key=KEY}
		<option value="{$KEY}" {if $MODULE == $KEY}selected{/if}>{$VALUE}</option>
		{/foreach}
	</select>
	<input type="submit" class="button" value="{sugar_translate label='LBL_GO_BUTTON_LABEL' module=''}" />
	<input type="hidden" value="wirelessmodule" name="action" />	
</form>