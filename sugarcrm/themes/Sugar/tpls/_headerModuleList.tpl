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
<div id="moduleList" class="yuimenubar yuimenubarnav">
	<div class="bd">
		<ul class="first-of-type">
		{foreach from=$moduleTopMenu item=module key=name name=moduleList}
			{if $name == $MODULE_TAB}
			<li class="yuimenubaritem {if $smarty.foreach.moduleList.index == 0}first-of-type{/if} current">{sugar_link id="moduleTab_$name" module=$name data=$module class="yuimenuitemlabel"}
			{else}
			<li class="yuimenubaritem {if $smarty.foreach.moduleList.index == 0}first-of-type{/if}">{sugar_link id="moduleTab_$name" module=$name data=$module class="yuimenuitemlabel"}
			{/if}
			{if $shortcutTopMenu.$name}
				<div id="{$name}" class="yuimenu dashletPanelMenu"><div class="bd">
				
										<ul class="shortCutsUl">
										<li class="yuimenuitem">{$APP.LBL_LINK_ACTIONS}</li>
										{foreach from=$shortcutTopMenu.$name item=shortcut_item}
										
											<li class="yuimenuitem"><a href="{$shortcut_item.URL}" class="yuimenuitemlabel">{$shortcut_item.LABEL}</a></li>
										
										{/foreach}
										</ul>
										<ul id="lastViewedContainer{$name}" class="lastViewedUl"><li class="yuimenuitem">{$APP.LBL_LAST_VIEWED}</li><li class="yuimenuitem" id="shortCutsLoading"><a href="#" class="yuimenuitemlabel">&nbsp;</a></li></ul>
								
				
				</div>
				<div class="clear"></div>
				</div>      
			{/if}
			</li>
			{if $name == $MODULE_TAB}
			<li class="yuimenubaritem currentTabRight">{sugar_link id="moduleTab_$name" module=$name data=$module class="yuimenuitemlabel"}</li>
			{/if}
		{/foreach}
			{if count($moduleExtraMenu) > 0}
			<li class="yuimenubaritem" id="moduleTabExtraMenu">
				<a href="#" class="yuimenuitemlabel more"><em>&gt;&gt;</em></a>
				<div id="More" class="yuimenu dashletPanelMenu"><div class="bd">
				<ul>
					{foreach from=$moduleExtraMenu item=module key=name name=moduleList}
					<li>{sugar_link id="moduleTab_$name" class="yuimenuitemlabel" module=$name data=$module}
					{/foreach}
				</ul>
				</div>
				<div class="clear"></div>
				</div> 
			</li>
			{/if}
		</ul>            
	</div>
</div>