{* Generic List View make-over *}

<div id='ibmBase_dashlet_{$id}' name='ibmBase_dashlet_{$id}' style='overflow: auto; width: 100%; border: 1px #ddd solid'>

<table width="100%" cellspacing="0" cellpadding="0" border="0" class="list view">
<tbody>

{if count($dataset_display)}
	<tr height="20">
		{foreach from=$listviewdefs item=col}
		<th width="" nowrap="nowrap" scope="col">
			<div align="left" width="100%" style="white-space: nowrap;">{$col.label}</div>
		</th>
		{/foreach}
	</tr>
	
	{foreach from=$dataset_display item=row}
		<tr height="20" class="oddListRowS1" id="row_{$row.id}">
			{foreach from=$row item=col}
				<td align="left" valign="top" scope="row">{$col}</td>
			{/foreach}
		</tr>
	{/foreach}
{else}
	<tr height="20">
		<th width="" nowrap="nowrap" scope="col">
			<div align="left" width="100%" style="white-space: nowrap;">No Data</div>
		</th>
	</tr>
{/if}
</table>
</div>
