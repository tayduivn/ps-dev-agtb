<table class="listView" width="80%">
  <tr>
	<td colspan=2 width="40%">
	  &nbsp;
	</td>
	<td>
	  {$mod.LBL_COL_WEIGHT}: {sugar_help text=$mod.LBL_HELP_WEIGHT WIDTH=500}
	</td>
	<td>
	  <input type="text" name="{$prefix}_weight" value="{$config.weight}" size=4 maxlength=3 onblur="recalcAll('{$prefix}');">
	</td>
	<td colspan=2>
	  &nbsp;
	</td>
  </tr>
  <tr>
	<th width="40%" class="listViewThS1" scope="col">
	  {$mod.LBL_RELATEDRULE_VALUE} {sugar_help text=$mod.LBL_HELP_COL_RELATE_VALUE WIDTH=500}
	</td>
	<th class="listViewThS1" scope="col">
	  {$mod.LBL_COL_SCORE}
	</td>
	<th class="listViewThS1" scope="col">
	  {$mod.LBL_COL_CALC_SCORE} {sugar_help text=$mod.LBL_HELP_COL_WEIGHTED WIDTH=500}
	</td>
	<th class="listViewThS1" scope="col">
	  {$mod.LBL_COL_MUL} {sugar_help text=$mod.LBL_HELP_COL_BOOST WIDTH=500}
	</td>
	<th class="listViewThS1" scope="col">
	  {$mod.LBL_COL_ENABLED} {sugar_help text=$mod.LBL_HELP_COL_ENABLE WIDTH=500}
	</td>
  </tr>
  {foreach from=$config.rows key=rowid item=row}
  <tr>
	{if $row.value == '_DEFAULT'}
	<td>
	  {$mod.LBL_RELATEDRULE_DEFAULT} {sugar_help text=$mod.LBL_RELATEDRULE_DEFAULT_HELP WIDTH=500}
	  <input type="hidden" name="{$prefix}_rows[{$rowid}][value]" value="_DEFAULT">
	</td>
	{else}
	<td>
	  <input type="text" size=6 maxlength=11 name="{$prefix}_rows[{$rowid}][value]" value="{$row.value}">
	</td>
	{/if}
	<td>
	  <input type="text" name="{$prefix}_rows[{$rowid}][score]" size=4 maxlength=8 value="{$row.score}" onkeyup="updateCalc(this,'{$prefix}_weight');">
	</td>
	<td>
	  <span id="{$prefix}_rows[{$rowid}][calc]">{$row.score*$config.weight}</span>
	</td>
	<td>
	  <input type="text" name="{$prefix}_rows[{$rowid}][mul]"
	  size=4 maxlength=8 value="{$row.mul|string_format:"%0.1f"}%">
	</td>
	<td>
	  <input type="checkbox" name="{$prefix}_rows[{$rowid}][enabled]" value="true"
	  {if $row.enabled}checked{/if}>
      {if $row.value != '_DEFAULT'}
	  <input type="image" src="{$image_path}delete_inline.gif" onclick="if(check_form('adminSettings')&&confirm('{$mod.LBL_DELETE_ROW}')) {ldelim} document.adminSettings.saveScoreConfigs.value='true'; document.adminSettings.deleteRowPrefix.value='{$prefix}'; document.adminSettings.deleteRow.value='{$rowid}'; document.adminSettings.submit(); {rdelim}">
      {/if}
	</td>
  </tr>
  {/foreach}
  <tr id="{$prefix}_newrow">
	<td>
	  <input type="text" size=6 maxlength=11 name="{$prefix}_rows[_NEW][value]" value="">
	</td>
	<td colspan="4">
	  <input type="button" onclick="document.adminSettings.saveScoreConfigs.value='true'; if(check_form('adminSettings')) {ldelim} document.adminSettings.submit(); {rdelim}" class="button" value="{$mod.LBL_ADD_VALUE}">
	</td>
  </tr>
</table>