
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
<!-- Logout -->
<hr />
<div id="footerlinks">
{if $VIEW != 'wirelessmain'}
<small><a href="index.php?module=Users&action=wirelessmain">{sugar_translate label='LBL_TABGROUP_HOME' module=''}</a></small> | 
{/if}
<small><a href="javascript:history.back();">{sugar_translate label='LBL_BACK' module=''}</a></small> |
{if $display_employees}
<small><a href="index.php?module=Employees&action=wirelessmodule">{sugar_translate label='LBL_EMPLOYEES' module=''}</a></small> |
{/if}
<small><a href="index.php?module=Users&action=Logout">{sugar_translate label='LBL_LOGOUT' module=''}</a></small>
</div>

</body>
</html>