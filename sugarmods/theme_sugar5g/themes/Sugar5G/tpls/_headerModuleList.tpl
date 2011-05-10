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
<div id="moduleList">
<ul>
    <li class="noBorder">&nbsp;</li>
    {assign var="groupSelected" value=false}
    {foreach from=$groupTabs item=modules key=group name=groupList}
    {capture name=extraparams assign=extraparams}parentTab={$group}{/capture}
    {if ( ( $smarty.request.parentTab == $group || (!$smarty.request.parentTab && in_array($MODULE_TAB,$modules.modules)) ) && !$groupSelected ) || ($smarty.foreach.groupList.index == 0 && $defaultFirst)}
    <li class="noBorder">
        <span class="currentTabLeft">&nbsp;</span><span class="currentTab">
            <a href="#" id="grouptab_{$smarty.foreach.groupList.index}">{$group}</a>
        </span><span class="currentTabRight">&nbsp;</span>
        {assign var="groupSelected" value=true}
    {else}
    <li>
        <span class="notCurrentTabLeft">&nbsp;</span><span class="notCurrentTab">
        <a href="#" id="grouptab_{$smarty.foreach.groupList.index}">{$group}</a>
        </span><span class="notCurrentTabRight">&nbsp;</span>
    {/if}
    </li>
    {/foreach}
</ul>
</div>
<div class="clear"></div>
<div id="subModuleList">
    {assign var="groupSelected" value=false}
    {foreach from=$groupTabs item=modules key=group name=moduleList}
    {capture name=extraparams assign=extraparams}parentTab={$group}{/capture}
    <span id="moduleLink_{$smarty.foreach.moduleList.index}" {if ( ( $smarty.request.parentTab == $group || (!$smarty.request.parentTab && in_array($MODULE_TAB,$modules.modules)) ) && !$groupSelected ) || ($smarty.foreach.moduleList.index == 0 && $defaultFirst)}class="selected" {assign var="groupSelected" value=true}{/if}>
    	<ul>
	        {foreach from=$modules.modules item=module}
	        <li>
	        	{capture name=moduleTabId assign=moduleTabId}moduleTab_{$smarty.foreach.moduleList.index}_{$module}{/capture}
	        	{sugar_link id=$moduleTabId module=$module data=$module extraparams=$extraparams}
	        </li>
	        {/foreach}
	        {if !empty($modules.extra)}
	        <li class="subTabMore">
	        	<a>>></a>      
		        <ul class="cssmenu">
		        {foreach from=$modules.extra item=submodule}
					<li>
						<a href="{sugar_link module=$submodule link_only=1 extraparams=$extraparams}">{$submodule}
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

