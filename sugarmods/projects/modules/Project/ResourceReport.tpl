{*
/**
 * EditView for Project
 *
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2005 SugarCRM, Inc.; All Rights Reserved.
 */

*}
<!-- $Id: EditView.html 16472 2006-08-27 10:33:09 +0000 (Sun, 27 Aug 2006) majed $ -->
<!-- BEGIN: main -->

<table width="100%" cellpadding="1" cellspacing="1" border="0" >
	<tr>
		<td style="padding-bottom: 2px;" colspan=6>
			<form name="ResourceView" id="ResourceView" method="post" action="index.php">
				<input type="hidden" name="module" value="Project" />
				<input type="hidden" name="sugar_body_only" id="sugar_body_only" value="1">
				<input type="hidden" name="record" id="record" value="{$ID}">
				<input type="hidden" name="action" id="action" value="ResourceReport" />
		</td>
	</tr>
	<tr>
		<td width="15%">{$MOD.LBL_LIST_RESOURCE} </td>
		<td><select id='resource' name='resource'>
			<option value="">----</option>
			{foreach from=$RESOURCES item="RESOURCE"}
				{if $SELECTED_RESOURCE == $RESOURCE->id}
					<option value="{$RESOURCE->id}" selected>{$RESOURCE->full_name}</option>
				{else}
					<option value="{$RESOURCE->id}">{$RESOURCE->full_name}</option>
				{/if}
			{/foreach}		
			</select>
		</td>
	</tr>
	<tr>
		<td>{$MOD.LBL_FILTER_DATE_RANGE_START} </td>
		<td><input name=date_start id=date_start onblur="parseDate(this, '{$CALENDAR_DATEFORMAT}');" type="input" tabindex='2' size='11' maxlength='10' value='{$DATE_START}' /> </td>
	</tr>
	<tr>
		<td>{$MOD.LBL_FILTER_DATE_RANGE_FINISH} </td>
		<td><input name=date_finish id=date_finish onblur="parseDate(this, '{$CALENDAR_DATEFORMAT}');" type="input" tabindex='2' size='11' maxlength='10' value='{$DATE_FINISH}' /> </td>
	</tr>
	<tr>
		<td colspan=2><input class="button" type="button" name="button" value="{$MOD.LBL_REPORT}" onclick="document.getElementById('ResourceView').submit();"  />
		</td>
	</tr>

</form>
</table>
<br/>
<h2>{$MOD.LBL_DAILY_REPORT}</h2>
<table id="resourceTable" border="0" cellpadding="1" cellspacing="1" class="tabDetailView2" width="25%">
	<tr height="25" align="center">
		<th width="10%">{$MOD.LBL_DATE}</th>
		<th width="10%">{$MOD.LBL_PERCENT_BUSY}</th>
	</tr>	
	{foreach from=$DATE_RANGE_ARRAY item="PERCENT" key="DATE"}
	<tr class="tabDetailViewDL2" align="center">
		<td>{$DATE}</td>
		{if $PERCENT >= 0}
			<td>{$PERCENT}</td>
		{else}
			<td>{$MOD.LBL_HOLIDAY}</td>
		{/if}
	</tr>
	{/foreach}
	
</table>
<br/>
<h2>{$MOD.LBL_PROJECT_TASK_SUBPANEL_TITLE}</h2>
<table id="resourceTable" border="0" cellpadding="1" cellspacing="1" class="tabDetailView2">
	<tr height="25" align="center">
		<th width="3%">{$MOD.LBL_TASK_ID}</th>
		<th width="15%" nowrap>{$MOD.LBL_MODULE_NAME}</th>
		<th width="25%" nowrap>{$MOD.LBL_TASK_NAME}</th>
		<th width="5%">{$MOD.LBL_PERCENT_COMPLETE}</th>
		<th width="5%">{$MOD.LBL_DURATION}</th>
		<th width="5%">{$MOD.LBL_START}</th>
		<th width="5%">{$MOD.LBL_FINISH}</th>
	</tr>	
	{foreach from=$TASKS item="TASK"}
	<tr class="tabDetailViewDL2" align="center">
		{assign var=project_id value=$TASK->project_id}
		<td>{$TASK->project_task_id}</td>
		<td>{$PROJECTS[$project_id]->name}</td>
		<td>{$TASK->name}</td>
		<td>{$TASK->percent_complete}</td>
		<td>{$TASK->duration} {$TASK->duration_unit}</td>
		<td>{$TASK->date_start}</td>
		<td>{$TASK->date_finish}</td>
	</tr>
	{/foreach}
	
</table>
<br/>
<h2>{$MOD.LBL_HOLIDAYS_TITLE}</h2>
<table id="holidaysTable" border="0" cellpadding="1" cellspacing="1" class="tabDetailView2" width="50%">
	<tr height="25" align="center">
		<th width="5%">{$MOD.LBL_DATE}</th>
		<th width="45%">{$MOD.LBL_MODULE_NAME}</th>
	</tr>	
	{foreach from=$HOLIDAYS item="HOLIDAY" key="i"}
	<tr class="tabDetailViewDL2" align="center">
		<td>{$HOLIDAY.holidayDate}</td>
		<td>{$HOLIDAY.projectName}</td>
	</tr>
	{/foreach}
</table>
<script type="text/javascript">
Calendar.setup ({literal}{{/literal}
	inputField : "date_start", ifFormat : '{$CALENDAR_DATEFORMAT}', showsTime : false, button : "date_start", singleClick : true, step : 1{literal}}{/literal});
Calendar.setup ({literal}{{/literal}
	inputField : "date_finish", ifFormat : '{$CALENDAR_DATEFORMAT}', showsTime : false, button : "date_finish", singleClick : true, step : 1{literal}}{/literal});
</script>

<!-- END: main -->