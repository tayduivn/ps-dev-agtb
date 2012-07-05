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

<script type="text/javascript">
sugar_theme_gm_current = '{$currentGroupTab}';
Set_Cookie('sugar_theme_gm_current','{$currentGroupTab}',30,'/','','');
</script>


{if $AJAX ne "1"}
<div id="moduleList">
{/if}
{* tab groups *}
{assign var='overflowSuffix' value='Overflow'}
{assign var='overflowHidden' value='Hidden'}
{foreach from=$groupTabs item=tabGroup key=tabGroupName name=tabGroups}
{php}
$tabGroupName = str_replace(" ", "_", $this->get_template_vars('tabGroupName'));
$currentGroupTab = str_replace(" ", "_", $this->get_template_vars('currentGroupTab'));
$this->assign('tabGroupName', $tabGroupName);
$this->assign('currentGroupTab', $currentGroupTab);
{/php}
  {* This is a little hack for Smarty, to make the ID's match up for compatibility *}
  {if $tabGroupName == $APP.LBL_TABGROUP_ALL}
  {assign var='groupTabId' value=''}
  {else}
  {assign var='groupTabId' value=$tabGroupName$underscore}
  {/if}
	<ul id="themeTabGroupMenu_{$tabGroupName}" class="sf-menu" style="{if $tabGroupName != $currentGroupTab}display:none;{/if}">
	{* visible menu items *}
	{foreach from=$tabGroup.modules item=module key=name name=moduleList}
	{if $name == "Home"}
		{assign var='homeImageLabel' value=$homeImage}
		{assign var='homeClass' value='home'}
		{assign var='title' value=$APP.LBL_TABGROUP_HOME}
	{else}
		{assign var='homeImageLabel' value=''}
		{assign var='homeClass' value=''}
		{assign var='title' value=''}
	{/if}
	
	{if $name == $MODULE_TAB}
		<li class="current {$homeClass}">{sugar_link id="moduleTab_$tabGroupName$name" module=$name data=$module label=$homeImageLabel title=$title class="sf-with-ul"}
	{else}
		<li class="{$homeClass}">{sugar_link id="moduleTab_$tabGroupName$name" module=$name data=$module label=$homeImageLabel title=$title class="sf-with-ul"}
	{/if}
		{if $shortcutTopMenu.$name }
		<ul class="megamenu">
		<li >
			<div class="megawrapper">
				<div class="megacolumn">
					<div class="megacolumn-content divider">
					<ul class="MMShortcuts">
					<li class="groupLabel">{$APP.LBL_LINK_ACTIONS}</li>
					{foreach from=$shortcutTopMenu.$name item=shortcut_item}
					  {if $shortcut_item.URL == "-"}
		              	<hr style="margin-top: 2px; margin-bottom: 2px" />
					  {else}
		                {if $module == "Calendar"}
					  		<li><a id="{$shortcut_item.LABEL|replace:' ':''}{$module}{$tabGroupName}" href="{sugar_ajax_url url=$shortcut_item.URL}">{$shortcut_item.LABEL}</a></li>
					  	{else}
		                	<li><a id="{$shortcut_item.LABEL|replace:' ':''}{$tabGroupName}" href="{sugar_ajax_url url=$shortcut_item.URL}">{$shortcut_item.LABEL}</a></li>
					  	{/if}
					  {/if}
					{/foreach}
					</ul>
					</div>
				</div>
				<div class="megacolumn">
					<div class="megacolumn-content divider">
					{if $groupTabId}
					<ul id="lastViewedContainer{$tabGroupName}_{$name}" class="MMLastViewed">
						<li class="groupLabel">{$APP.LBL_LAST_VIEWED}</li>
						<li id="shortCutsLoading{$tabGroupName}_{$name}"><a href="#">&nbsp;</a></li>
					</ul>
					{else}
					<ul id="lastViewedContainer{$name}" class="MMLastViewed">
						<li class="groupLabel">{$APP.LBL_LAST_VIEWED}</li>
						<li id="shortCutsLoading{$tabGroupName}_{$name}"><a href="#">&nbsp;</a></li>
					</ul>
					{/if}
					</div>
				</div>
                <div class="megacolumn">
                    <div class="megacolumn-content">
                    <ul class="MMFavorites">
                        <li class="groupLabel">{$APP.LBL_FAVORITES}</li>
                        <li><a href="javascript: void(0);">&nbsp;</a></li>
                    </ul>
                    </div>
                </div>
			</div>
		</li>
		</ul>
		{/if}
	</li>
	{/foreach}
	
	
	{* more menu items overlfow *}
	
		<li class="moduleTabExtraMenu more" id="moduleTabExtraMenu{$tabGroupName}">
		<a href="javascript: void(0);" class="sf-with-ul more"><span style="float: left;">{$APP.LBL_MORE}</span><em>&gt;&gt;</em></a>
		<ul class="megamenu moduleTabExtraMenu">
		<li>
			<div class="megawrapper">
				<div class="megacolumn">
					<div class="megacolumn-content">
							<ul id="moduleTabMore{$tabGroupName}" class="showLess moduleTabMore megamenuSiblings">
							
							{* visible overflow menu items *}
							{foreach from=$tabGroup.extra item=name key=module name=moduleList}
				
							<li {if $smarty.foreach.moduleList.index > 4}class="moreOverflow"{/if}>{sugar_link id="moduleTab_$tabGroupName$module" module="$module" data="$name" class="sf-with-ul"}
								{if $shortcutExtraMenu.$module}
								<ul class="megamenu">
								<li >
									<div class="megawrapper">
										<div class="megacolumn">
											<div class="megacolumn-content divider">
											<ul class="MMShortcuts">
											<li class="groupLabel">{$APP.LBL_LINK_ACTIONS}</li>
											{foreach from=$shortcutExtraMenu.$module item=shortcut_item}
											  {if $shortcut_item.URL == "-"}
								              	<hr style="margin-top: 2px; margin-bottom: 2px" />
											  {else}
											  	{if $module == "Calendar"}
											  		<li><a id="{$shortcut_item.LABEL|replace:' ':''}{$module}{$tabGroupName}" href="{sugar_ajax_url url=$shortcut_item.URL}">{$shortcut_item.LABEL}</a></li>
											  	{else}
								                	<li><a id="{$shortcut_item.LABEL|replace:' ':''}{$tabGroupName}" href="{sugar_ajax_url url=$shortcut_item.URL}">{$shortcut_item.LABEL}</a></li>
											  	{/if}
											  {/if}
											{/foreach}
											</ul>
											</div>
										</div>
										<div class="megacolumn">
											<div class="megacolumn-content divider">
											{if $groupTabId}
											<ul id="lastViewedContainer{$tabGroupName}_{$name}" class="MMLastViewed">
												<li class="groupLabel">{$APP.LBL_LAST_VIEWED}</li>
												<li id="shortCutsLoading{$tabGroupName}_{$name}"><a href="#">&nbsp;</a></li>
											</ul>
											{else}
											<ul id="lastViewedContainer{$name}" class="MMLastViewed">
												<li class="groupLabel">{$APP.LBL_LAST_VIEWED}</li>
												<li id="shortCutsLoading{$tabGroupName}_{$name}"><a href="#">&nbsp;</a></li>
											</ul>
											{/if}
											</div>
										</div>
                                        <div class="megacolumn">
                                            <div class="megacolumn-content">
                                                <ul class="MMFavorites">
                                                    <li class="groupLabel">{$APP.LBL_FAVORITES}</li>
                                                    <li><a href="#">&nbsp;</a></li>
                                                </ul>
                                            </div>
                                        </div>
									</div>
								</li>
								</ul>
								{/if}
								</li>
							{/foreach}
							{if count($tabGroup.extra) > 5}
							<li class="moduleMenuOverFlowMore" id="moduleMenuOverFlowMore{$currentGroupTab}"><a href="javascript: SUGAR.themes.toggleMenuOverFlow('moduleTabMore{$currentGroupTab}','more');">{$APP.LBL_SHOW_MORE} <div class="showMoreArrow"></div></a></li>
							<li class="moduleMenuOverFlowLess" id="moduleMenuOverFlowLess{$currentGroupTab}"><a href="javascript: SUGAR.themes.toggleMenuOverFlow('moduleTabMore{$currentGroupTab}','less');">{$APP.LBL_SHOW_LESS} <div class="showLessArrow"></div></a></li>
							{/if}
							</ul>		
							<ul class="filterBy megamenuSiblings">
							{* group modules *}
							{if $USE_GROUP_TABS}
					 		{if count($tabGroup.extra) > 0}
					 		<li class="menuHR"></li>
					 		{/if}
				
					 		<li><a href="#" class="group sf-with-ul" title="{$tabGroupName}">{$APP.LBL_FILTER_MENU_BY}</a>
					
								<ul class="sf-menu filter-menu">
						          {foreach from=$groupTabs item=module key=group name=groupList}
				                      {php}
				                          $group = str_replace(" ", "_", $this->get_template_vars('group'));
				                          $this->assign('group_id', $group);
				                      {/php}
						          <li {if $tabGroupName eq $group}class="selected"{/if}><a href="javascript:(SUGAR.themes.sugar_theme_gm_switch('{$group}', '{$group_id}') && false)" class="{if $tabGroupName eq $group}selected{/if}">{$group}</a></li>
						          {/foreach}
								</ul>
				   				</li>
				        	</ul>
					 		{/if}
					 		</div>
				 		</div>
			 		</div>
		 		</li>
			</ul>
		</li>
	
	
</ul>
{/foreach}
{if $AJAX ne "1"}
</div>
{/if}
