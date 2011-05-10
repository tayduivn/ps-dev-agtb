


<table cellpadding='0' cellspacing='0' width='100%' border='0' class='listView'>		
	{foreach name=rowIteration from=$data key=id item=rowData}
		{if $smarty.foreach.rowIteration.iteration is odd}
			{assign var='_bgColor' value=$bgColor[0]}
			{assign var='_rowColor' value=$rowColor[0]}
		{else}
			{assign var='_bgColor' value=$bgColor[1]}
			{assign var='_rowColor' value=$rowColor[1]}
		{/if}
		<tr height='20' onmouseover="setPointer(this, '{$id}', 'over', '{$_bgColor}', '{$bgHilite}', '');" onmouseout="setPointer(this, '{$id}', 'out', '{$_bgColor}', '{$bgHilite}', '');" onmousedown="setPointer(this, '{$id}', 'click', '{$_bgColor}', '{$bgHilite}', '');">
			{foreach from=$displayColumns key=col item=params}
				<td scope='row' align='{$params.align|default:'left'}' valign=top class='{$_rowColor}S1' bgcolor='{$_bgColor}'>
					{if $params.link && !$params.customCode}				
						<{$pageData.tag.$id[$params.ACLTag]|default:$pageData.tag.$id.MAIN} href='index.php?action={$params.action|default:'DetailView'}&module={if $params.dynamic_module}{$rowData[$params.dynamic_module]}{else}{$params.module|default:$pageData.module}{/if}&id={$rowData[$params.id]|default:$id}&offset={$pageData.offsets.current+$smarty.foreach.rowIteration.iteration}&stamp={$pageData.stamp}&returnAction={$returnAction}&returnModule={$returnModule}&returnId={$returnId}' class='listViewTdLinkS1'>{$rowData.$col}</{$pageData.tag.$id[$params.ACLTag]|default:$pageData.tag.$id.MAIN}>
					{elseif $params.customCode}
						{sugar_evalcolumn var=$params.customCode rowData=$rowData}
					{elseif $pageData.checkboxes.$col}
						{if $rowData.$col == '1'}
							<input type='checkbox' checked disabled='disabled'>
						{else}
							<input type='checkbox' disabled='disabled'>						
						{/if}
					{else}
						{$rowData.$col}
					{/if}
				</td>
			{/foreach}
    	</tr>
	 	<tr><td colspan='20' class='listViewHRS1'></td></tr>
	{/foreach}
</table>