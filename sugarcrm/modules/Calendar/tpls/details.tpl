<form id="CalendarEditView" name="CalendarEditView" method="POST">	
		
<input type="hidden" name="current_module" id="current_module" value="Meetings">
<input type="hidden" name="record" id="record" value="">
<input type="hidden" name="user_invitees" id="user_invitees">
<input type="hidden" name="contact_invitees" id="contact_invitees">
<input type="hidden" name="lead_invitees" id="lead_invitees">
<input type="hidden" name="send_invites" id="send_invites">

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
	if(form.duration_hours.value + form.duration_minutes.value <= 0){
		alert('{/literal}{$MOD.NOTICE_DURATION_TIME}{literal}'); 
		return false; 
	} 
	return true;
}
{/literal}
</script>
<script type="text/javascript" src="include/SugarFields/Fields/Datetimecombo/Datetimecombo.js"></script>
