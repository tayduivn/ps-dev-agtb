
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
<p>{$MESSAGE}</p>
<form action="index.php" method="POST">
{foreach from=$REQUEST_VALS key=name item=value}
<input type='hidden' name='{$name}' value='{$value}'>
{/foreach}
<input type='hidden' name="action" value="wirelessedit">
<input type='hidden' name="failsave" value="1">
<input type="submit" value="{sugar_translate label='LBL_BACK' module=''}" />
</form>