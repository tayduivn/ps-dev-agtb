<table class="listView" width="80%">
  <tr>
	<td width="40%">
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
	  {$mod.LBL_CHECKBOXRULE_FIELDVALUE}
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
	<td>
	  {if $row.value == '_CHECKED'}
	  {$mod.LBL_CHECKBOXRULE_CHECKED}<input type="hidden"
	  name="{$prefix}_rows[{$rowid}][value]" value="_CHECKED">
	  {else}
	  {$mod.LBL_CHECKBOXRULE_UNCHECKED}<input type="hidden"
	  name="{$prefix}_rows[{$rowid}][value]" value="_UNCHECKED">
	  {/if}
	</td>
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
	</td>
  </tr>
  {/foreach}
</table>