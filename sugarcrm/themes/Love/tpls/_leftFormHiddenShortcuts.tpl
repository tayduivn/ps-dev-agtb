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
{if $LEFT_FORM_SHORTCUTS}
<div id="hiddenShortcuts" class="leftList" onmouseover="hiliteItem(this,'no');">
    <ul class="shortcuts">
    <li class="TopBorder">
    	<span class="TopBorderLeft"></span>
    </li>
    {if $USE_GROUP_TABS}
    {assign var="groupSelected" value=false}
    {foreach from=$groupTabs item=modules key=group name=groupList}
    {if ( in_array($MODULE_TAB,$modules.modules) && !$groupSelected ) || ($smarty.foreach.groupList.index == 0 && $defaultFirst)}
    <li class="currentShortcut">
        <a href="{sugar_link module=$modules.modules.0 link_only=1}" 
            id="grouptab_{$smarty.foreach.groupList.index}">{$group}</a>
        {foreach from=$modules.modules item=module}
        <li class="noBorder subshortcut">
            <a href="{sugar_link module=$module link_only=1}">{$moduleNames.$module}</a>
        </li>
        {/foreach}
        {foreach from=$subMoreModules.$group.modules item=submodule}
        <li class="noBorder subshortcut">
            <a href="{sugar_link module=$submodule link_only=1}">{$submodule}</a>
        </li>
        {/foreach}
    	{assign var="groupSelected" value=true}
    {else}
    <li class="notCurrentShortcut">
        <a href="{sugar_link module=$modules.modules.0 link_only=1}" 
            id="grouptab_{$smarty.foreach.groupList.index}">{$group}</a>
        <ul class="cssmenu">
            {foreach from=$modules.modules item=module}
            <li class="noBorder subshortcut">
                <a href="{sugar_link module=$module link_only=1}">{$moduleNames.$module}</a>
            </li>
            {/foreach}
            {foreach from=$subMoreModules.$group.modules item=submodule}
            <li class="noBorder subshortcut">
                <a href="{sugar_link module=$submodule link_only=1}">{$submodule}</a>
            </li>
            {/foreach}
        </ul>
    {/if}
    </li>
    {/foreach}
    <li class="BottomBorder">
        <span class="BottomBorderLeft"></span>
    </li>
    {else}
    {foreach from=$moduleTopMenu item=module key=name name=moduleList}
    {if $name == $MODULE_TAB}
	    <li class="currentShortcut">
	        {sugar_link id="moduleTab_$name" module=$name}
		</li>
		{if $name!='Home'}
		    {counter start=1 name="num" assign="num"}
		    {foreach from=$SHORTCUT_MENU item=item}
			    <li class="subshortcut">
			        <a href="{$item.URL}">{$item.IMAGE}&nbsp;
			        	<span>{$item.LABEL}</span>
			    	</a>
			    </li>
			    {counter name="num"}
		    {/foreach}
	    {/if}
	{else}
    <li class="notCurrentShortcut">
       {sugar_link id="moduleTab_$name" module=$name}
    {/if}
    </li>
    {/foreach}
    {if count($moduleExtraMenu) > 0}
        {foreach from=$moduleExtraMenu item=module key=name name=moduleList}
        {if $name == $MODULE_TAB}
		<li class="noBorder subshortcut">
		    {sugar_link id="moduleTab_$name" module=$name}
		{else}
		<li class="notCurrentShortcut">
		    {sugar_link id="moduleTab_$name" module=$name}
		{/if}
		</li>
        {/foreach}
        <li class="BottomBorder">
        <span class="BottomBorderLeft"></span>
    </li>
    
    {/if}
    {/if}
</ul>
</div>
{/if}