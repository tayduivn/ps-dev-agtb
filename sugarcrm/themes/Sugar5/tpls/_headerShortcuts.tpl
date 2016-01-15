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
{* FG - Bug 41467 - Let Home module have Shortcuts *}
{if count($SHORTCUT_MENU) > 0}
<div id="shortcuts" class="headerList">
    <b style="white-space:nowrap;">{$APP.LBL_LINK_ACTIONS}:&nbsp;&nbsp;</b>
    <span>
    {foreach from=$SHORTCUT_MENU item=item}
    <span style="white-space:nowrap;">
        {if $item.URL == "-"}
          <a></a><span>&nbsp;</span>
        {else}
          <a href="{$item.URL}">{$item.IMAGE}&nbsp;<span>{$item.LABEL}</span></a>
        {/if}
    </span>
    {/foreach}
    </span>
</div>
{/if}
