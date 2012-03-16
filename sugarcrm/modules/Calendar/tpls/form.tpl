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
<form id="CalendarEditView" name="CalendarEditView" method="POST">	
		
<input type="hidden" name="current_module" id="current_module" value="Meetings">
<input type="hidden" name="record" id="record" value="">
<input type="hidden" name="full_form" value="">
<input type="hidden" name="user_invitees" id="user_invitees">
<input type="hidden" name="contact_invitees" id="contact_invitees">
<input type="hidden" name="lead_invitees" id="lead_invitees">
<input type="hidden" name="send_invites" id="send_invites">


<input type="hidden" name="edit_all_recurrences" id="edit_all_recurrences">
<input type="hidden" name="repeat_parent_id" id="repeat_parent_id">
<input type="hidden" name="repeat_type" id="repeat_type">
<input type="hidden" name="repeat_interval" id="repeat_interval">
<input type="hidden" name="repeat_count" id="repeat_count">
<input type="hidden" name="repeat_until" id="repeat_until">
<input type="hidden" name="repeat_dow" id="repeat_dow">


<div style="padding: 4px 0; font-size: 12px;">
	{literal}
	<input type="radio" id="radio_meeting" value="Meetings" onclick="CAL.change_activity_type(this.value);" checked="true"  name="appttype" tabindex="100"/>
	{/literal}
	<label for="radio_meeting">{$MOD.LBL_CREATE_MEETING}</label>
	{literal}
	<input type="radio" id="radio_call" value="Calls" onclick="CAL.change_activity_type(this.value);" name="appttype" tabindex="100"/>
	{/literal}
	<label for="radio_call">{$MOD.LBL_CREATE_CALL}</label>											
</div>

<div id="form_content">
	<input type="hidden" name="date_start" id="date_start" value="{$user_default_date_start}">
	<input type="hidden" name="duration_hours" id="duration_hours">
	<input type="hidden" name="duration_minutes" id="duration_minutes">
</div>

</form>

<script type="text/javascript">
enableQS(false);
{literal}
function cal_isValidDuration(){ 
	form = document.getElementById('CalendarEditView');
	if(typeof form.duration_hours == "undefined" || typeof form.duration_minutes == "undefined")
		return true;
	if(form.duration_hours.value + form.duration_minutes.value <= 0){
		alert('{/literal}{$MOD.NOTICE_DURATION_TIME}{literal}'); 
		return false; 
	} 
	return true;
}
{/literal}
</script>
<script type="text/javascript" src="include/SugarFields/Fields/Datetimecombo/Datetimecombo.js"></script>
