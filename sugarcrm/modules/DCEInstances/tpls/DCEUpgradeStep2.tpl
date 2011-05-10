{*

/**
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
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */



*}
<p>
{$MODULE_TITLE}
</p>
<form enctype="multipart/form-data" name="dceupgradestep2" method="POST" action="index.php" id="dceupgradestep2">
<input type="hidden" name="module" value="DCEInstances">
<input type="hidden" name="action" value="">
<input type="hidden" name="uid" value="{$UIDS}">
<input type="hidden" name="record" value="">
<input type="hidden" name='return_action' value="{$RETURN_ACTION}">
<input type="hidden" name='return_id' value="{$RETURN_ID}">
<input type="hidden" name="actionType" value="upgrade">
<input type="hidden" name="delete_clone" value="false">

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="edit view">
<tr>
<td>
	<table border="0" cellspacing="0" cellpadding="0" width="100%">
        <tr>
            <td scope="row">
            {$MOD.LBL_FROM_TEMPLATE}:
            </td>
            <td >
                {$FROMTEMPLATE}
            </td>
        </tr>
		<tr>
			<td scope="row">
			{$MOD.LBL_TO_TEMPLATE}:
			</td>
			<td >
			{if $TOTEMPLATEDD}
			 {html_options name=totemplate options=$TOTEMPLATEDD}
			{else}
			 <font color='red'>{$MOD.ERR_NO_TEMPLATE_AVAILABLE}</font>
			 <input type="hidden" name="totemplate" id="totemplate" value="">
			{/if}
			</td>
		</tr>
		<tr>
			<td scope="row">
			{$MOD.LBL_DATE_PLANNED}:
			</td>
			<td >
				<table border="0" cellpadding="0" cellspacing="0">
				<tr valign="middle">
				<td nowrap>
				<input autocomplete="off" type="text" id="startDate_date" value="" size="11" maxlength="10" onblur="combo_startDate.update();">
				<input type="hidden" id="startDate" name="startDate" value="">
				{sugar_getimage name="jscalendar" ext=".gif" alt="{$app_strings.LBL_JS_CALENDAR}" other_attributes='align="absmiddle" border="0" id="startDate_trigger" '}&nbsp;
				</td>
				<td nowrap>
				<div id="startDate_time_section"></div>
				</td>
				</tr>
				<tr valign="middle">
				<td nowrap>
				<span class="dateFormat">{$USER_DATEFORMAT}</span>
				</td>
				<td nowrap>
				<span class="dateFormat">{$TIME_FORMAT}</span>
				</td>
				</tr>
				</table>
				<script type="text/javascript" src="{sugar_getjspath file='include/SugarFields/Fields/Datetimecombo/Datetimecombo.js'}"></script>
				<script type="text/javascript">
				var combo_startDate = new Datetimecombo("{$DEFAULT_DATE}", "startDate", "{$TIME_FORMAT}", 0, '', ''); 
				//Render the remaining widget fields
				text = combo_startDate.html('SugarWidgetScheduler.update_time();');
				document.getElementById('startDate_time_section').innerHTML = text;
				
				//Call eval on the update function to handle updates to calendar picker object
				eval(combo_startDate.jsscript('SugarWidgetScheduler.update_time();'));
				</script>
				<script type="text/javascript">
				Calendar.setup ({ldelim}
				onClose : update_startDate,
				inputField : "startDate_date",
				ifFormat : "{$CALENDAR_FORMAT}",
				daFormat : "{$CALENDAR_FORMAT}",
				button : "startDate_trigger",
				singleClick : true,
				step : 1,
				weekNumbers:false
				{rdelim});
				
				//Call update for first time to round hours and minute values
				combo_startDate.update();
				</script>
			</td>
		</tr>
        <tr>
            <td scope="row" valign='top' >
            {$MOD.LBL_UPGRADE_OPTIONS}:
            </td>
            <td >
                <div>
                <label for="upgrade_live">
                    <input type="radio" style="radio" name="upgrade_type" id="upgrade_live" value="upgrade_live" onchange="update_options()" CHECKED>
                    {$MOD.LBL_UPGRADE_LIVE}
                </label>
                </div>
                <div>
                <label for="upgrade_test">
                    <input type="radio" style="radio" name="upgrade_type" id="upgrade_test" value="upgrade_test" onchange="update_options()">
                    {$MOD.LBL_UPGRADE_TO_CLONE}
                </label>
                </div>
                <div id="save_clone_on_error_div">
                <label for="save_clone_on_error">
                    <input type="checkbox" style="checkbox" name="save_clone_on_error" id="save_clone_on_error" CHECKED>
                    {$MOD.LBL_CLONE_ON_ERROR}
                </label>
                </div>
            </td>
        </tr>
	</table>
</td>
</tr>
</table>
<br>
{$LISTVIEW}
<br>
<table width="100%" cellpadding="0" cellspacing="0" border="0">
<tr>
	<td align="left">
        <input title="{$MOD.LBL_BACK}" accessKey="" class="button" type="submit" name="button" value="  {$MOD.LBL_BACK}  " id="goback">&nbsp;
	    {if $TOTEMPLATEDD}
	    <input title="{$MOD.LBL_NEXT}" accessKey="" class="button" type="submit" name="button" value="  {$MOD.LBL_NEXT}  " id="gonext">
        {else}
        <input title="{$MOD.LBL_NEXT}" class="button" type="button" name="button" value="  {$MOD.LBL_NEXT}  " id="gonext" disabled>
        {/if}
    </td>
</tr>
</table>

</form>
{$JAVASCRIPT}
