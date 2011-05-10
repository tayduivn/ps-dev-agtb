{*
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
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