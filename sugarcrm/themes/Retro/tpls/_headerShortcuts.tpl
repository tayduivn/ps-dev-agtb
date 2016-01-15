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
<div id="shortcuts" class="headerList">
    <ul>
    {assign var='i' value=1}
    {foreach from=$SHORTCUT_MENU item=item}
    {if $i<8}
    <li style="white-space:nowrap;">
        <a href="{$item.URL}">{$item.IMAGE}&nbsp;<span>{$item.LABEL}</span></a>
    </li>
    {else}
        {if $i==8}
        <li id="shortcutsextra" class="moduleTabExtraMenu">
        <a href="#">&nbsp;&nbsp;</a>
        <ul id="shortcutsextramenu" class="shortcutsextramenu">
        {/if}
        <li><a href="{$item.URL}">{$item.IMAGE}&nbsp;<span>{$item.LABEL}</span></a></li>
        {if $i==count($SHORTCUT_MENU)}
        </ul>
        </li>
        {/if}
    {/if}
    {assign var='i' value=$i+1}
    {/foreach}
    </ul>
</div>
{/if}