<table class="moduleTitle" border="0" cellpadding="0" cellspacing="0" width="100%">
<tbody>
<tr><td valign="top"><h2>Call Assistant Settings:</h2></td>
</tr>
</tbody>
</table>
<br>
<form action="index.php" method="post" name="index.php">
<input type="hidden" name="module" value="Administration" />
<input type="hidden" name="action" />
<input type="hidden" name="version" value="{$VERSION}" />
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td style="padding-bottom: 2px;">
	<input type="submit" name="save" class="button"
		title="{$APP.LBL_SAVE_BUTTON_TITLE}"
		accesskey="{$APP.LBL_SAVE_BUTTON_KEY}"
		onclick="this.form.action.value='ConfigureCASettings';"
		value="{$APP.LBL_SAVE_BUTTON_LABEL}"
	/>
	<input type="submit" name="button" class="button"
		title="{$APP.LBL_CANCEL_BUTTON_TITLE}"
		accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}"
		onclick="this.form.action.value='index'; this.form.module.value='Administration';"
		value="{$APP.LBL_CANCEL_BUTTON_LABEL}"
	/>
</td>
</tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0" class="edit view">
<tr><td>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
	<td width="20%" scope="row">{$MOD.LBL_SHOW_PLANNED_CALLS}</td>
	<td width="30%" scope="row"><input type="hidden" name="show_planned_calls" value="false"><input type="checkbox" id="show_planned_call_trigger" name="show_planned_calls" value="true" {$SHOW_PLANNED_CALLS_CHECKED} onClick="togglePlannedCallPeriod()"></td>
	<td width="20%" scope="row">{$MOD.LBL_SHOW_RELATED_OPPORTUNITIES}</td>
	<td width="30%" scope="row"><input type="hidden" name="show_related_opportunities" value="false"><input type="checkbox" id="show_related_opp_trigger" name="show_related_opportunities" value="true" {$SHOW_RELATED_OPPORTUNITIES_CHECKED} onClick="toggleOppFilter()"></td>
	</tr>
	<tr>
	<td scope="row"><span id="planned_call_period_lbl">{$MOD.LBL_PLANNED_CALL_PERIOD}</span> <img border="0" src="themes/default/images/helpInline.gif" onmouseover="return helpText('Determines how far in the future to look for planned Calls.')" onmouseout="return nd();"></td>
	<td scope="row"><select id="planned_call_period" name="planned_call_period">{$PLANNED_CALL_PERIOD_OPTIONS}</select></td>
	<td scope="row"><span id="opp_status_filter_lbl">{$MOD.LBL_OPPORTUNITY_STATUS_EXCLUDE}</span> <img border="0" src="themes/default/images/helpInline.gif" onmouseover="return helpText('Call Assistant will not display Opportunities with these selected stages.')" onmouseout="return nd();"></td>
	<td scope="row"><select id='opp_status_filter' name='opportunity_status_exclude[]' size='3' multiple='multiple'>{$OPPORTUNITY_STATUS_EXCLUDE_OPTIONS}</select></td>
	</tr>
	<tr>
	<td scope="row">{$MOD.LBL_SHOW_RELATED_CASES}</td>
	<td scope="row"><input type="hidden" name="show_related_cases" value="false"><input type="checkbox" id="show_related_case_trigger" name="show_related_cases" value="true" {$SHOW_RELATED_CASES_CHECKED} onClick="toggleCaseFilter()"></td>
	<td scope="row">{$MOD.LBL_SHOW_RELATED_ACCOUNT_CONTACTS}</td>
	<td scope="row"><input type="hidden" name="show_related_account_contacts" value="false"><input type="checkbox" name="show_related_account_contacts" value="true" {$SHOW_RELATED_ACCOUNT_CONTACTS_CHECKED}></td>
	</tr>
	<tr>
	<td scope="row"><span id="case_status_filter_lbl">{$MOD.LBL_CASE_STATUS_EXCLUDE}</span> <img border="0" src="themes/default/images/helpInline.gif" onmouseover="return helpText('Call Assistant will not display Cases with these selected statuses.')" onmouseout="return nd();"></td>
	<td scope="row"><select id="case_status_filter" name="case_status_exclude[]" size="3" multiple="multiple">{$CASE_STATUS_EXCLUDE_OPTIONS}</select></td>
	<td scope="row">{$MOD.LBL_LEAD_STATUS_EXCLUDE} <img border="0" src="themes/default/images/helpInline.gif" onmouseover="return helpText('Call Assistant will not display Leads with these selected statuses.')" onmouseout="return nd();"></td>
	<td scope="row"><select name='lead_status_exclude[]' size='3' multiple='multiple'>{$LEAD_STATUS_EXCLUDE_OPTIONS}</select></td>
	</tr>
	</table>
</td></tr></table>
<table cellpadding="0" cellspacing="0" border="0" width="100%">
<tr>
<td style="padding-bottom: 2px;">
	<input type="submit" name="save" class="button"
		title="{$APP.LBL_SAVE_BUTTON_TITLE}"
		accesskey="{$APP.LBL_SAVE_BUTTON_KEY}"
		onclick="this.form.action.value='ConfigureCASettings';"
		value="{$APP.LBL_SAVE_BUTTON_LABEL}"
	/>
	<input type="submit" name="button" class="button"
		title="{$APP.LBL_CANCEL_BUTTON_TITLE}"
		accessKey="{$APP.LBL_CANCEL_BUTTON_KEY}"
		onclick="this.form.action.value='index'; this.form.module.value='Administration';"
		value="{$APP.LBL_CANCEL_BUTTON_LABEL}"
	/>
</td>
</tr>
</table>
</form>
<br>
<h3>Call Assistant Simulator</h3>
<form name="demoform" method="post" action="UAECallAssistant.php">
<input type="hidden" name="action" value="UAECallAssistant" />
<input type="hidden" name="opt" value="1" />
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="edit view">
<tr><td>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
	<td width="20%" scope="row">{$MOD.LBL_CA_PHONE}</td>
	<td width="30%" scope="row"><input type="text" name="phone" /></td>
	<td width="20%" scope="row">&nbsp;</td>
	<td width="30%" scope="row">&nbsp;</td>
	</tr><tr>
	<td scope="row">{$MOD.LBL_CA_DIRECTION}</td>
	<td scope="row"><select name="direction"><option value="Inbound">Inbound</option>
															<option value="Outbound">Outbound</option>
															</select></td>
	<td scope="row">&nbsp;</td>
	<td scope="row">&nbsp;</td>
	</tr>
	<tr>
	<td colspan="4" scope="row"><input type="submit" class="button" name="submit" value="Simulate" /></td>
	</tr>
	</table>
</td></tr>
</table>
</form>
<br/>
<h3>HUD Web Launcher</h3>
<table width="100%" border="0" cellspacing="0" cellpadding="0" class="edit view">
<tr><td>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr><td scope="row" colspan="2">To enable the UAE Call Assistant, you will need to configure HUD as follows for each agent:</td></tr>
	<tr>
		<td width="2%" scope="row" nowrap>Step 1.</td>
		<td width="98%" align="left" scope="row">Open HUD</td>
	</tr>
	<tr>
		<td scope="row" nowrap>Step 2.</td>
		<td scope="row">Select&nbsp;&nbsp;<b>Settings</b> option from the <b>File</b> menu</td>
	</tr>
	<tr>
		<td scope="row" nowrap>Step 3.</td>
		<td scope="row">Select&nbsp;&nbsp;<b>Web Launcher</b></td>
	</tr>
	<tr valign="top">
		<td scope="row" nowrap>Step 4.</td>
		<td scope="row">Under <b>Outbound Calls</b>, enter the URL below in the <b>Launch web page below during new calls:</b> field and check the <b>Auto-launch page</b> checkbox above it.<div style="height:8px"><br></div>
		<i>{$SERVER_NAME}/UAECallAssistant.php?action=UAECallAssistant&direction=Outbound&phone=%%caller_number%%</i></td>
	</tr>
	<tr valign="top">
		<td scope="row" nowrap>Step 5.</td>
		<td scope="row">Under <b>Inbound Calls</b>, enter the URL below in the <b>Launch web page below during new calls:</b> field and check the <b>Auto-launch page</b> checkbox above it.<div style="height:8px"><br></div>
		<i>{$SERVER_NAME}/UAECallAssistant.php?action=UAECallAssistant&direction=Inbound&phone=%%caller_number%%</i></td>
	</tr>
	<tr>
		<td scope="row" nowrap>Step 6.</td>
		<td scope="row">Click the <b>Apply</b> button</td>
	</tr>
	</table>
</td></tr>
</table>
<script type='text/javascript' src='include/javascript/sugar_grp_overlib.js'></script>
<script type="text/javascript">
{literal}
function helpText(txt){
	return overlib(txt, FGCLASS, 'olFgClass', CGCLASS, 'olCgClass', BGCLASS, 'olBgClass', TEXTFONTCLASS, 'olFontClass', CAPTIONFONTCLASS, 'olCapFontClass', CLOSEFONTCLASS, 'olCloseFontClass', WIDTH, -1, NOFOLLOW, 'ol_nofollow' );
}

function togglePlannedCallPeriod(){
	if(document.getElementById('show_planned_call_trigger').checked){
		document.getElementById('planned_call_period_lbl').style.color = '#444444';
		document.getElementById('planned_call_period').disabled = false;
	} else {
		document.getElementById('planned_call_period_lbl').style.color = 'gray';
		document.getElementById('planned_call_period').disabled = true;
	}
}

function toggleOppFilter(){
	if(document.getElementById('show_related_opp_trigger').checked){
		document.getElementById('opp_status_filter_lbl').style.color = '#444444';
		document.getElementById('opp_status_filter').disabled = false;
	} else {
		document.getElementById('opp_status_filter_lbl').style.color = 'gray';
		document.getElementById('opp_status_filter').disabled = true;
	}
}

function toggleCaseFilter(){
	if(document.getElementById('show_related_case_trigger').checked){
		document.getElementById('case_status_filter_lbl').style.color = '#444444';
		document.getElementById('case_status_filter').disabled = false;
	} else {
		document.getElementById('case_status_filter_lbl').style.color = 'gray';
		document.getElementById('case_status_filter').disabled = true;
	}
}

togglePlannedCallPeriod();
toggleOppFilter();
toggleCaseFilter();
{/literal}
</script>