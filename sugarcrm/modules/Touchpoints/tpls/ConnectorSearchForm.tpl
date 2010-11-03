<input type='hidden' name='source_id' id='source_id' value='{$source_id}'>
<input type='hidden' name='merge_module' value='{$module}'>
<input type='hidden' name='record' value='{$RECORD}'>
<h4 class="dataLabel">{$MOD.LBL_STEP1}</h4>
<br>
 	<table width="100%" cellspacing="0" cellpadding="0" border="0">
{if !empty($search_fields) }
 	 <tr>
 	 {counter assign=field_count start=0 print=0} 
	 {foreach from=$search_fields key=field_name item=field_value} 
	 	{counter assign=field_count}
		{if ($field_count % 3 == 1 && $field_count != 1)}
		</tr><tr>
		{/if}
		<td nowrap="nowrap" width='10%'>{$field_value.label}:&nbsp;</td>
		<td nowrap="nowrap" width='30%'><input type='text' onkeydown='checkKeyDown(event);' name='{$field_name}' value='{$field_value.value}'></td>
	 {/foreach}
	 </tr>
{else}
     {$MOD.ERROR_NO_SEARCHDEFS_MAPPING}
{/if}
    </table>
<br>
<input type='button' name='btn_search' id='btn_search' title="{$APP.LBL_SEARCH_BUTTON_LABEL}" accessKey="{$APP.LBL_SEARCH_BUTTON_KEY}" class="button" onClick="SourceTabs.search();" value="      {$APP.LBL_SEARCH_BUTTON_LABEL}      ">&nbsp;
<input type='button' name='btn_clear' title="{$APP.LBL_CLEAR_BUTTON_LABEL}" accessKey="{$APP.LBL_CLEAR_BUTTON_KEY}" class="button" onClick="SourceTabs.clearForm();" value="{$APP.LBL_CLEAR_BUTTON_LABEL}">