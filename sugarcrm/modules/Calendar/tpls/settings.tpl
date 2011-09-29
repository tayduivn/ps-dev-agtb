<div id="settings_dialog" style="width: 340px; display: none;">
	<div class="hd">{$MOD.LBL_SETTINGS_TITLE}</div>
	<div class="bd">
	<form name="settings" id="form_settings" method="POST" action="index.php?module=Calendar&action=SaveSettings">
		<input type="hidden" name="view" value="{$pview}">
		<input type="hidden" name="day" value="{$day}">
		<input type="hidden" name="month" value="{$month}">
		<input type="hidden" name="year" value="{$year}">
		
		<table class='edit view tabForm'>
				<tr>
					<td scope="row" valign="top">
						{$MOD.LBL_SETTINGS_TIME_STARTS}
					</td>
					<td>
						<div id="d_start_time_section">
							<select size="1" id="d_start_hours" name="d_start_hours" tabindex="102">
								{$TIME_START_HOUR_OPTIONS1}
							</select>&nbsp;:
							
							<select size="1" id="d_start_minutes" name="d_start_minutes"  tabindex="102">
								{$TIME_START_MINUTES_OPTIONS1}
							</select>
								&nbsp;
							{$TIME_MERIDIEM1}
						</div>
					</td>
				</tr>
				<tr>
					<td scope="row" valign="top">
						{$MOD.LBL_SETTINGS_TIME_ENDS}
					</td>
					<td>
						<div id="d_end_time_section">
							<select size="1" id="d_end_hours" name="d_end_hours" tabindex="102">
								{$TIME_START_HOUR_OPTIONS2}
							</select>&nbsp;:
							
							<select size="1" id="d_end_minutes" name="d_end_minutes"  tabindex="102">
								{$TIME_START_MINUTES_OPTIONS2}
							</select>
								&nbsp;
							{$TIME_MERIDIEM2}
						</div>
					</td>
				</tr>
				<tr>
					<td scope="row" valign="top">
						{$MOD.LBL_SETTINGS_CALLS_SHOW}
					</td>
					<td>	
						<select size="1" name="show_calls" tabindex="102">
							<option value='' {if !$show_calls}selected{/if}>{$MOD.LBL_NO}</option>
							<option value='true' {if $show_calls}selected{/if}>{$MOD.LBL_YES}</option>								
						</select>
					</td>
				</tr>
				<tr>
					<td scope="row" valign="top">
						{$MOD.LBL_SETTINGS_TASKS_SHOW}
					</td>
					<td>	
						<select size="1" name="show_tasks" tabindex="102">
							<option value='' {if !$show_tasks}selected{/if}>{$MOD.LBL_NO}</option>
							<option value='true' {if $show_tasks}selected{/if}>{$MOD.LBL_YES}</option>								
						</select>
					</td>
				</tr>
		</table>
	</form>
	
	
	<div style="text-align: right;">
		<button id="btn_save_settings" class="button" type="button">{$MOD.LBL_APPLY_BUTTON}</button>&nbsp;
		<button id="btn_cancel_settings" class="button" type="button">{$MOD.LBL_CANCEL_BUTTON}</button>&nbsp;
	</div>
	</div>
</div>
