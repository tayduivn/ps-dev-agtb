{*
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
*}
{if !$LEFT_FORM_SHORTCUTS}
<div id="hiddenShortcuts" class="leftList">
    <h3><span>{$APP.LBL_SHORTCUTS}</span></h3>
    <ul onmouseover="hiliteItem(this,'no');">
	{foreach from=$SHORTCUT_MENU item=item}
    <li>
        <a href="{$item.URL}">{$item.IMAGE}&nbsp;<span>{$item.LABEL}</span></a>
    </li>
    {/foreach}
    </ul>
</div>
{/if}
