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

{if $controls}

<div class="clear"></div>

<div style='float:left; width: 50%;'>
{foreach name=tabs from=$tabs key=k item=tab}
	<input type="button" class="button" {if $view == $tab} selected {/if} id="{$tabs_params[$tab].id}" title="{$tabs_params[$tab].title}" value="{$tabs_params[$tab].title}" onclick="{$tabs_params[$tab].link}">
{/foreach}
</div>

<div style="float:left; text-align: right; width: 50%; font-size: 12px;">
	{if $view == "shared"}
		<button id="userListButtonId" type="button" class="button" onclick="javascript: CAL.toggle_shared_edit('shared_cal_edit');">{$MOD.LBL_EDIT_USERLIST}</button>
	{/if}
	{if $view != 'year' && !$print}
	<span class="dateTime">
					<img border="0" src="{$cal_img}" alt="{$APP.LBL_ENTER_DATE}" id="goto_date_trigger" align="absmiddle">
					<input type="hidden" id="goto_date" name="goto_date" value="{$current_date}">
					<script type="text/javascript">
					Calendar.setup ({literal}{{/literal}
						inputField : "goto_date",
						ifFormat : "%m/%d/%Y",
						daFormat : "%m/%d/%Y",
						button : "goto_date_trigger",
						singleClick : true,
						dateStr : "{$current_date}",
						step : 1,
						onUpdate: goto_date_call,
						startWeekday: {$start_weekday},
						weekNumbers:false
					{literal}}{/literal});
					{literal}
					YAHOO.util.Event.onDOMReady(function(){
						YAHOO.util.Event.addListener("goto_date","change",goto_date_call);
					});
					function goto_date_call(){
						CAL.goto_date_call();
					}
					{/literal}
					</script>
	</span>
	{/if}
	<input type="button" id="cal_settings" class="button" onclick="CAL.toggle_settings()" value="{$MOD.LBL_SETTINGS}">
</div>

<div style='clear: both;'></div>

{/if}


<div class="{if $controls}monthHeader{/if}">
	<div style='float: left; width: 20%;'>{$previous}</div>
	<div style='float: left; width: 60%; text-align: center;'><h3>{$date_info}</h3></div>
	<div style='float: right;'>{$next}</div>
	<br style='clear:both;'>
</div>
