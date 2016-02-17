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
{if$LEFT_FORM_SHORTCUTS}
<div id="subshortcuts" class="headerList">
    <ul>
    {counter start=1 name="num" assign="num"}
    {foreach from=$SHORTCUT_MENU item=item}
    {if $num<9 }
    <li class="subshortcut"><a href="{$item.URL}">{$item.IMAGE}<span>{$item.LABEL}</span></a></li>
    {else}
        {if $num==9}
        	<li id="extra_shortuct_button" class="shortcutstabextramenu">
        	<a href="#">&gt;&gt;</a><br />
    		<ul id="extra_shortuct_menu" class="shortcutsextramenu">
    	{/if}
	        <li class="subshortcut" ><a href="{$item.URL}">{$item.IMAGE}<span>{$item.LABEL}</span></a></li>
        {if $num==count($SHORTCUT_MENU)}
        	</ul>
        	</li>
        {/if}
    {/if}
    {counter name="num"}
    {/foreach}
    </ul>
</div>
{/if}