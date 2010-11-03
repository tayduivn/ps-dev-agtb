{*

/**
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 **/
*}
{$themeHTML}
<h2>{$mod.LBL_SB_TITLE}</h2>
<table class="listView" border=0 cellspacing=0 cellpadding=4>
  <tr>
	<td class="listViewThS1" scope="col">{$mod.LBL_SB_TH_FIELD}</td>
	<td class="listViewThS1" scope="col">{$mod.LBL_SB_TH_VALUE}</td>
	<td class="listViewThS1" scope="col">{$mod.LBL_SB_TH_SCORE}</td>
	<td class="listViewThS1" scope="col">{$mod.LBL_SB_TH_MUL}</td>
  </tr>
  {foreach name=rowLoop from=$scoreRows key=i item=row}
  <tr class="{if $smarty.foreach.rowLoop.iteration is odd}odd{else}even{/if}ListRowS1">
	<td class="{if $smarty.foreach.rowLoop.iteration is odd}odd{else}even{/if}ListRowS1">{$row.name}</td>
	<td class="{if $smarty.foreach.rowLoop.iteration is odd}odd{else}even{/if}ListRowS1">{$row.val}</td>
	<td class="{if $smarty.foreach.rowLoop.iteration is odd}odd{else}even{/if}ListRowS1">{$row.score}</td>
	<td class="{if $smarty.foreach.rowLoop.iteration is odd}odd{else}even{/if}ListRowS1">{$row.mul}%</td>
  </tr>
  {/foreach}
  <tr>
	<td class="listViewTHS1" scope="col" colspan=2 style="text-align: right;">
	  {$mod.LBL_SB_TOTAL}{if not $multThis} {$mod.LBL_SB_MULT_PARENT} {/if}:
	</td>
	<td class="listViewTHS1" scope="col">{$totalScore}</td>
	<td class="listViewTHS1" scope="col">{$totalMul|string_format:"%0.1f"}%</td>
  </tr>
</table>