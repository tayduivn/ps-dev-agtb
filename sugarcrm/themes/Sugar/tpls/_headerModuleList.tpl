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
{assign var='underscore' value='_'}
{if $AJAX ne "1"}
<div class="yuimenubar yuimenubarnav" id="moduleList">
{/if}
{foreach from=$groupTabs item=tabGroup key=tabGroupName name=tabGroups}
  {* This is a little hack for Smarty, to make the ID's match up for compatibility *}
  {if $tabGroupName == 'All'}
  {assign var='groupTabId' value=''}
  {else}
  {assign var='groupTabId' value=$tabGroupName$underscore}
  {/if}
  
  
	<div id="themeTabGroupMenu_{$tabGroupName}" class="themeTabGroupMenu yuimenubar yuimenubarnav"><div class="bd" id="themeTabGroup_{$tabGroupName}"  style="border: 0px !important; margin: 0px !important;{if $tabGroupName != $currentGroupTab}display:none;{/if}">
      <ul class="first-of-type">


		{foreach from=$tabGroup.modules item=module key=name name=moduleList}
			{if $name == $MODULE_TAB}
				{if $name == "Home"}
				<li class="yuimenubaritem {if $smarty.foreach.moduleList.index == 0}first-of-type{/if} current home">{sugar_link id="moduleTab_$tabGroupName$name" module=$name data=$module label=$homeImage class="yuimenuitemlabel" title=$name}
				{else}
				<li class="yuimenubaritem {if $smarty.foreach.moduleList.index == 0}first-of-type{/if} current">{sugar_link id="moduleTab_$tabGroupName$name" module=$name data=$module class="yuimenuitemlabel"}
				{/if}
			{else}
				{if $name == "Home"}
				<li class="yuimenubaritem {if $smarty.foreach.moduleList.index == 0}first-of-type{/if} home">{sugar_link id="moduleTab_$tabGroupName$name" module=$name data=$module label=$homeImage class="yuimenuitemlabel" title=$name}
				{else}
				<li class="yuimenubaritem {if $smarty.foreach.moduleList.index == 0}first-of-type{/if}">{sugar_link id="moduleTab_$tabGroupName$name" module=$name data=$module class="yuimenuitemlabel"}
				{/if}
			{/if}
			{if $shortcutTopMenu.$name && $name != "Home"}
				<div id="{$groupTabId}{$name}" class="yuimenu dashletPanelMenu"><div class="bd">
				
										<ul class="shortCutsUl">
										<li class="yuimenuitem">{$APP.LBL_LINK_ACTIONS}</li>
										{foreach from=$shortcutTopMenu.$name item=shortcut_item}
										  {if $shortcut_item.URL == "-"}
                                            <hr style="margin-top: 2px; margin-bottom: 2px" />
										  {else}
                                             <li class="yuimenuitem"><a href="{sugar_ajax_url url=$shortcut_item.URL}" class="yuimenuitemlabel">{$shortcut_item.LABEL}</a></li>
										  {/if}
										{/foreach}
										</ul>
										<ul class="favoritesUl" id="favoritesUl">
											<li class="yuimenuitem">Favorites</li>
											<li class="yuimenuitem"><a href="#" class="yuimenuitemlabel">Favorite Record One</a></li>
										</ul>
										{if $groupTabId}
										<ul id="lastViewedContainer{$tabGroupName}_{$name}" class="lastViewedUl"><li class="yuimenuitem">{$APP.LBL_LAST_VIEWED}</li><li class="yuimenuitem" id="shortCutsLoading{$tabGroupName}_{$name}"><a href="#" class="yuimenuitemlabel">&nbsp;</a></li></ul>
										{else}
										<ul id="lastViewedContainer{$name}" class="lastViewedUl"><li class="yuimenuitem">{$APP.LBL_LAST_VIEWED}</li><li class="yuimenuitem" id="shortCutsLoading{$tabGroupName}_{$name}"><a href="#" class="yuimenuitemlabel">&nbsp;</a></li></ul>
										{/if}
								
				
				</div>
				<div class="clear"></div>
				</div>      
			{/if}
			</li>
		{/foreach}
			
			<li class="yuimenubaritem moduleTabExtraMenu more showLess" id="moduleTabExtraMenu{$tabGroupName}">
				<a href="#" class="yuimenuitemlabel more"><span style="float: left;">More</span><em>&gt;&gt;</em></a>
				<div id="More{$tabGroupName}" class="yuimenu dashletPanelMenu"><div class="bd">
				<ul>

					{foreach from=$tabGroup.extra item=name key=module name=moduleList}

					<li {if $smarty.foreach.moduleList.index > 4}class="moreOverflow"{/if}>{sugar_link id="moduleTab_$groupTabId$module" class="yuimenuitemlabel" module="$module" data="$name"}
						{if $shortcutTopMenu.$name}
							<div id="{$groupTabId}{$name}" class="yuimenu dashletPanelMenu"><div class="bd">
										<ul class="shortCutsUl">
										<li class="yuimenuitem">{$APP.LBL_LINK_ACTIONS}</li>
										{foreach from=$shortcutTopMenu.$name item=shortcut_item}
										  {if $shortcut_item.URL == "-"}
                                            <hr style="margin-top: 2px; margin-bottom: 2px" />
										  {else}
                                             <li class="yuimenuitem"><a href="{sugar_ajax_url url=$shortcut_item.URL}" class="yuimenuitemlabel">{$shortcut_item.LABEL}</a></li>
										  {/if}
										{/foreach}
										</ul>
										<ul class="favoritesUl" id="favoritesUl">
											<li class="yuimenuitem">Favorites</li>
											<li class="yuimenuitem"><a href="#" class="yuimenuitemlabel">Favorite Record One</a></li>
										</ul>
										{if $groupTabId}
										<ul id="lastViewedContainer{$tabGroupName}_{$name}" class="lastViewedUl"><li class="yuimenuitem">{$APP.LBL_LAST_VIEWED}</li><li class="yuimenuitem" id="shortCutsLoading{$tabGroupName}_{$name}"><a href="#" class="yuimenuitemlabel">&nbsp;</a></li></ul>
										{else}
										<ul id="lastViewedContainer{$name}" class="lastViewedUl"><li class="yuimenuitem">{$APP.LBL_LAST_VIEWED}</li><li class="yuimenuitem" id="shortCutsLoading{$tabGroupName}_{$name}"><a href="#" class="yuimenuitemlabel">&nbsp;</a></li></ul>
										{/if}
							</div>
							<div class="clear"></div>
							</div> 
						{/if}
						</li>
					{/foreach}
					{if count($tabGroup.extra) > 5}
					<li class="moduleMenuOverFlowMore" id="moduleMenuOverFlowMore{$currentGroupTab}"><a class="yuimenuitemlabel" href="javascript: toggleMenuOverFlow('moduleTabExtraMenu{$currentGroupTab}','more');">Show More <img src="{sugar_getimagepath file="advanced_search.gif"}"></a></li>
					<li class="moduleMenuOverFlowLess" id="moduleMenuOverFlowMore{$currentGroupTab}"><a class="yuimenuitemlabel" href="javascript: toggleMenuOverFlow('moduleTabExtraMenu{$currentGroupTab}','less');">Show Less <img src="{sugar_getimagepath file="basic_search.gif"}"></a></li>
					{/if}
					
	 {if $USE_GROUP_TABS}
	 
	 	{if count($tabGroup.extra) > 0}
	 		<li class="menuHR"></li>
	 	{/if}
        <script type="text/javascript">
        sugar_theme_gm_current = '{$currentGroupTab}';
        Set_Cookie('sugar_theme_gm_current','{$currentGroupTab}',30,'/','','');
        </script>
        {* Tab group selection *}
        <li class="">
        <a href="#" class="yuimenuitemlabel more group" title="{$tabGroupName}">Filter Menu By</a>
        <div id="TabGroupMenu_{$tabGroupName}" class="yuimenu dashletPanelMenu groupMenu"><div class="bd">
			<ul>
          {foreach from=$groupTabs item=module key=group name=groupList}
          <li {if $tabGroupName eq $group}class="yuimenuitem yuimenuitem-selected selected"{/if}><a href="javascript:(sugar_theme_gm_switch('{$group}') && false)" class="yuimenuitemlabel{if $tabGroupName eq $group} yuimenuitemlabel-selected selected{/if}">{$group}</a></li>
          {/foreach}
		  </ul>
        </div><div class="clear"></div></div> 
        </li>
      {/if}
      
      
				</ul>
				</div>
				<div class="clear"></div>
				</div> 
			</li>
			
			
		</ul>            
	</div></div>
{/foreach}

{if $AJAX ne "1"}
</div>
{/if}
