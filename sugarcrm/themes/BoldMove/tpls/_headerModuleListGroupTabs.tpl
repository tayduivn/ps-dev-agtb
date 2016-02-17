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
<div id="moduleList">
<ul>
    <li class="noBorder">&nbsp;</li>
    {assign var="groupSelected" value=false}
    {foreach from=$groupTabs item=modules key=group name=groupList}
    {capture name=extraparams assign=extraparams}parentTab={$group}{/capture}
    {if ( ( $parentTab == $group || (!$parentTab && in_array($MODULE_TAB,$modules.modules)) ) && !$groupSelected ) || ($smarty.foreach.groupList.index == 0 && $defaultFirst)}
    <li class="noBorder">
        <span class="currentTabLeft">&nbsp;</span><span class="currentTab" {if $smarty.foreach.groupList.last}style="border-right: 1px solid;"{/if}>
            <a href="{sugar_link module=$modules.modules.0 data=$modules.modules.0 link_only=1 extraparams=$extraparams}"
                id="grouptab_{$smarty.foreach.groupList.index}">{$group}</a>
        </span><span class="currentTabRight">&nbsp;</span>
        {assign var="groupSelected" value=true}
    {else}
    <li>
        <span class="notCurrentTabLeft">&nbsp;</span><span class="notCurrentTab" {if $smarty.foreach.groupList.last}style="border-right: 1px solid;"{/if}>
        <a href="{sugar_link module=$modules.modules.0 data=$modules.modules.0 link_only=1 extraparams=$extraparams}"
            id="grouptab_{$smarty.foreach.groupList.index}">{$group}</a>
        </span><span class="notCurrentTabRight">&nbsp;</span>
    {/if}
    </li>
    {/foreach}
</ul>
</div>
<div class="clear"></div>
<div id="subtabs" class="subTabBar">


<div id="subModuleList"  class="subTabBar">
    {assign var="groupSelected" value=false}
    {foreach from=$groupTabs item=modules key=group name=moduleList}
    {capture name=extraparams assign=extraparams}parentTab={$group}{/capture}
    <span id="moduleLink_{$smarty.foreach.moduleList.index}" {if ( ( $parentTab == $group || (!$parentTab && in_array($MODULE_TAB,$modules.modules)) ) && !$groupSelected ) || ($smarty.foreach.moduleList.index == 0 && $defaultFirst)}class="selected" {assign var="groupSelected" value=true}{/if}>
    	<ul>
	        {foreach from=$modules.modules item=module}
	        <li>
	        	{capture name=moduleTabId assign=moduleTabId}moduleTab_{$smarty.foreach.moduleList.index}_{$module}{/capture}
	        	{sugar_link id=$moduleTabId module=$module data=$module}
	        </li>
	        {/foreach}
	        {if $subMoreModules.$group.modules}
	        <li class="subTabMore">
	        	<a>>></a>
		        <ul class="cssmenu">
		        {foreach from=$subMoreModules.$group.modules item=submodule}
					<li>
						<a href="{sugar_link module=$submodule link_only=1 extraparams=$extraparams}" class="menuItem">{$moduleNames.$submodule}
						</a>
					</li>
		        {/foreach}
		        </ul>
	        </li>
	        {/if}
        </ul>
    </span>
    {/foreach}
</div>
	{include file="_headerSearch.tpl" theme_template=true}
</div>