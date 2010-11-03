<div id="div_merge_panel">
	<form name="MergeForm" method="POST" id="MergeForm" action="index.php">
	<script type='text/javascript'>
		var index = 0;
		var sourceIndex = 0;
		var fieldArray = new Array();
		var sourceArray = new Array();
	</script>
	<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td align="left" style="padding-bottom: 2px;">
		<input class="button" onclick="show_search_form();" type="button" name="smartCopy" value="{$mod.LBL_STEP1}">
		<input class="button" onclick="smart_copy();" type="button" name="smartCopy" value="{$mod.LBL_SMART_COPY}">
		</td>
		<td align="right" nowrap></td>
	</tr>
	</table>
	<table class="tabDetailView" cellspacing="0" cellpadding="0" border="0" width="100%">
	<tr>
		<td width='12.5%' class="tabDetailViewDL"></td>
	{foreach from=$source_names item=source_name}
		<td class="tabDetailViewDF">
		{if $source_name.id}
		<input class="button" onclick="copy_all('{$source_name.id}', '{$source_name.color}');" type="button" name="copyValue" value="<<">&nbsp;
		<script type='text/javascript'>
			sourceArray[sourceIndex] = '{$source_name.id}';
			sourceIndex++;
		</script>
		{/if}
		<b>{$source_name.name}</b>
		</td>
		
	{/foreach}
	</tr>
	{foreach from=$merge_fields key=field_name item=label}
		<tr>
			<td width='12.5%' class="tabDetailViewDL">
			{sugar_translate label="$label" module="$merge_module"}
			</td>
			
			<td class="tabDetailViewDF">
			   {if $field_defs[$field_name].type == 'enum' && !empty($field_defs[$field_name].options)}
			   {assign var="options" value=$field_defs[$field_name].options}
				   <select name="{$field_name}_copy" id="{$field_name}_copy" onchange="document.ScrubView['{$field_name}'].value = document.getElementById('{$field_name}' + '_copy').value; if('{$field_name}'.substring(0, 8) == 'primary_') document.getElementById('display_' + '{$field_name}'.substring(8)).innerHTML = document.getElementById('{$field_name}' + '_copy').value; if('{$field_name}' == 'annual_revenue2_c') document.getElementById('{$field_name}').onchange();">
						{if isset($record->$field_name)}
						{html_options options=$app_list_strings.$options selected=$record->$field_name}
						{else}
						{html_options options=$app_list_strings.$options}
						{/if}
				   </select>			   
			   {elseif $field_defs[$field_name].type == 'text'}
			   	   <textarea name="{$field_name}_copy" id="{$field_name}_copy" cols="35" rows="4" onkeyup="document.ScrubView['{$field_name}'].value = document.getElementById('{$field_name}' + '_copy').value; if('{$field_name}'.substring(0, 8) == 'primary_') document.getElementById('display_' + '{$field_name}'.substring(8)).innerHTML = document.getElementById('{$field_name}' + '_copy').value;">{$record->$field_name}</textarea>
			   {else}
			       <input name="{$field_name}_copy" id="{$field_name}_copy" size="35" maxlength="150" type="text" value="{$record->$field_name}" onkeyup="document.ScrubView['{$field_name}'].value = document.getElementById('{$field_name}' + '_copy').value; if('{$field_name}'.substring(0, 8) == 'primary_') document.getElementById('display_' + '{$field_name}'.substring(8)).innerHTML = document.getElementById('{$field_name}' + '_copy').value;">
			   {/if}
			</td>

			{foreach from=$result_beans key=source item=bean_entry}
			<td class="tabDetailViewDF" nowrap>
				<input class="button" onclick="copy_value('{$field_name}', '{$source}_{$field_name}', '{$source_names.$source.color}');" type="button" name="copyValue" value="<<"/>&nbsp;
				{if isset($bean_entry->$field_name)}
				<input name="{$source}_{$field_name}" id="{$source}_{$field_name}" size="35" maxlength="150" type="text" value="{$bean_entry->$field_name|replace:'"':'\''}" style='background:#{$source_names.$source.color}'>
				{else}
				<input name="{$source}_{$field_name}" id="{$source}_{$field_name}" size="35" maxlength="150" type="text" value="">
				{/if}
				<script language='javascript'>
					fieldArray[index] = '{$field_name}';
					index++;
				</script>
			</td>
			{/foreach}
		</tr>
	{/foreach}
	</table>
	<table width="100%" cellpadding="0" cellspacing="0" border="0">
	<tr>
		<td align="left" style="padding-bottom: 2px;">
		<input class="button" onclick="show_search_form();" type="button" name="smartCopy" value="{$mod.LBL_STEP1}">		
		<input class="button" onclick="smart_copy();" type="button" name="smartCopy" value="{$mod.LBL_SMART_COPY}">
		</td>
		<td align="right" nowrap></td>
	</tr>
	</table>
	</form>
</div>
