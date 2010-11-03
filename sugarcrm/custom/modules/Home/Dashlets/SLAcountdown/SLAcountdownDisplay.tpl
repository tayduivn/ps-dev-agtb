{if $refreshTime > '59999'}
<script>
{literal}
if ( typeof( window[ 't' ] ) != "undefined" ) {

} else {
{/literal}
	var t = window.setInterval('SUGAR.mySugar.retrieveDashlet("{$dashletId}", "{$pageData.urls.startPage}")',{$refreshTime});
{literal}
}
{/literal}
</script>
{/if}
<table cellpadding='0' cellspacing='0' width='100%' border='0' class='listView'>

{if $enableSerach == true}
    <tr heitght=shops'20'>
    	<td colspan='{$colCount+1}' align='left' class='listViewPaginationTdS1'>
    		<form>
    		<table border='0' cellpadding='0' cellspacing='0' width='100%'>
    			<tr>
    				<td>{$CASE_NUM} <input type="text" name='case_number' id='case_number'></td>
    				<td>{$ACCOUNT}: <input type="text" name='account_name' id='account_name{$id}' class='sqsEnabled'></td>
    				<td align="right"><input type="button" onclick='SLAcountdown.search("{$dashletId}")' value="Search" name='submit'></td>
    			</form>
    			</tr>
    		</table>

    	</td>
    </tr>
{/if}
    <tr>
        <td colspan='{$colCount+1}' align='right'>
            <table border='0' cellpadding='0' cellspacing='0' width='100%'>
                <tr>
<td align='left' class='listViewPaginationTdS1'>&nbsp;</td>
                    <td class='listViewPaginationTdS1' align='right' nowrap='nowrap' id='listViewPaginationButtons'>                    
                        {if $pageData.urls.startPage}
                            <!--<a href='#' onclick='return SUGAR.mySugar.retrieveDashlet("{$dashletId}", "{$pageData.urls.startPage}")' class='listViewPaginationLinkS1'><img src='{$imagePath}start.gif' alt='{$navStrings.start}' align='absmiddle' border='0' width='13' height='11'>&nbsp;{$navStrings.start}</a>&nbsp;-->
							<button title='{$navStrings.start}' class='button' onclick='return SUGAR.mySugar.retrieveDashlet("{$dashletId}", "{$pageData.urls.startPage}")'>
								<img src='{$imagePath}start.gif' alt='{$navStrings.start}' align='absmiddle' border='0' width='13' height='11'>
							</button>
                            
                        {else}
                            <!--<img src='{$imagePath}start_off.gif' alt='{$navStrings.start}' align='absmiddle' border='0' width='13' height='11'>&nbsp;{$navStrings.start}&nbsp;&nbsp;-->
							<button title='{$navStrings.start}' class='button' disabled>
								<img src='{$imagePath}start_off.gif' alt='{$navStrings.start}' align='absmiddle' border='0' width='13' height='11'>
							</button>
                            
                        {/if}
                        {if $pageData.urls.prevPage}
                            <!--<a href='#' onclick='return SUGAR.mySugar.retrieveDashlet("{$dashletId}", "{$pageData.urls.prevPage}")' class='listViewPaginationLinkS1'><img src='{$imagePath}previous.gif' alt='{$navStrings.previous}' align='absmiddle' border='0' width='8' height='11'>&nbsp;{$navStrings.previous}</a>&nbsp;-->
							<button title='{$navStrings.previous}' class='button' onclick='return SUGAR.mySugar.retrieveDashlet("{$dashletId}", "{$pageData.urls.prevPage}")'>
								<img src='{$imagePath}previous.gif' alt='{$navStrings.previous}' align='absmiddle' border='0' width='8' height='11'>							
							</button>
                            
                        {else}
                            <!--<img src='{$imagePath}previous_off.gif' alt='{$navStrings.previous}' align='absmiddle' border='0' width='8' height='11'>&nbsp;{$navStrings.previous}&nbsp;-->
							<button class='button' disabled title='{$navStrings.previous}'>
								<img src='{$imagePath}previous_off.gif' alt='{$navStrings.previous}' align='absmiddle' border='0' width='8' height='11'>
							</button>
                        {/if}
                            <span class='pageNumbers'>({if $pageData.offsets.lastOffsetOnPage == 0}0{else}{$pageData.offsets.current+1}{/if} - {$pageData.offsets.lastOffsetOnPage} {$navStrings.of} {if $pageData.offsets.totalCounted}{$pageData.offsets.total}{else}{$pageData.offsets.total}{if $pageData.offsets.lastOffsetOnPage != $pageData.offsets.total}+{/if}{/if})</span>
                        {if $pageData.urls.nextPage}
                            <!--&nbsp;<a href='#' onclick='return SUGAR.mySugar.retrieveDashlet("{$dashletId}", "{$pageData.urls.nextPage}")' class='listViewPaginationLinkS1'>{$navStrings.next}&nbsp;<img src='{$imagePath}next.gif' alt='{$navStrings.next}' align='absmiddle' border='0' width='8' height='11'></a>&nbsp;-->
							<button title='{$navStrings.next}' class='button' onclick='return SUGAR.mySugar.retrieveDashlet("{$dashletId}", "{$pageData.urls.nextPage}")'>
								<img src='{$imagePath}next.gif' alt='{$navStrings.next}' align='absmiddle' border='0' width='8' height='11'>
							</button>
                            
                        {else}
                           <!-- &nbsp;{$navStrings.next}&nbsp;<img src='{$imagePath}next_off.gif' alt='{$navStrings.next}' align='absmiddle' border='0' width='8' height='11'>-->
							<button class='button' title='{$navStrings.next}' disabled>
								<img src='{$imagePath}next_off.gif' alt='{$navStrings.next}' align='absmiddle' border='0' width='8' height='11'>
							</button>

                        {/if}
						{if $pageData.urls.endPage  && $pageData.offsets.total != $pageData.offsets.lastOffsetOnPage}
                            <!--<a href='#' onclick='return SUGAR.mySugar.retrieveDashlet("{$dashletId}", "{$pageData.urls.endPage}")' class='listViewPaginationLinkS1'>{$navStrings.end}&nbsp;<img src='{$imagePath}end.gif' alt='{$navStrings.end}' align='absmiddle' border='0' width='13' height='11'></a></td>-->
							<button title='{$navStrings.end}' class='button' onclick='return SUGAR.mySugar.retrieveDashlet("{$dashletId}", "{$pageData.urls.endPage}")'>
								<img src='{$imagePath}end.gif' alt='{$navStrings.end}' align='absmiddle' border='0' width='13' height='11'>							
							</button>
                            
						{elseif !$pageData.offsets.totalCounted || $pageData.offsets.total == $pageData.offsets.lastOffsetOnPage}
                            <!--&nbsp;{$navStrings.end}&nbsp;<img src='{$imagePath}end_off.gif' alt='{$navStrings.end}' align='absmiddle' border='0' width='13' height='11'>-->
							<button class='button' disabled title='{$navStrings.end}'>
							 	<img src='{$imagePath}end_off.gif' alt='{$navStrings.end}' align='absmiddle' border='0' width='13' height='11'>
							</button>
                            
                        {/if}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr height='20'>
        {foreach from=$displayColumns key=colHeader item=params}
	        <td scope='col' width='{$params.width}%' class='listViewThS1' nowrap>
				<div style='white-space: nowrap;'width='100%' align='{$params.align|default:'left'}'>
                {if $params.sortable|default:true}
	                <a href='#' onclick='return SUGAR.mySugar.retrieveDashlet("{$dashletId}", "{$pageData.urls.orderBy}{$params.orderBy|default:$colHeader|lower}&sugar_body_only=1&id={$dashletId}")' class='listViewThLinkS1'>{sugar_translate label=$params.label module=$pageData.bean.moduleDir}&nbsp;&nbsp;
	                {if $params.orderBy|default:$colHeader|lower == $pageData.ordering.orderBy}
	                    {if $pageData.ordering.sortOrder == 'ASC'}
	                        <img border='0' src='{$imagePath}arrow_down.{$arrowExt}' width='{$arrowWidth}' height='{$arrowHeight}' align='absmiddle' alt='{$arrowAlt}'>
	                    {else}
	                        <img border='0' src='{$imagePath}arrow_up.{$arrowExt}' width='{$arrowWidth}' height='{$arrowHeight}' align='absmiddle' alt='{$arrowAlt}'>
	                    {/if}
	                {else}
	                    <img border='0' src='{$imagePath}arrow.{$arrowExt}' width='{$arrowWidth}' height='{$arrowHeight}' align='absmiddle' alt='{$arrowAlt}'>
	                {/if}
	                </a>
	           {else}
	           		{sugar_translate label=$params.label module=$pageData.bean.moduleDir}
	           {/if}
			   </div>
            </td>
        {/foreach}
		{if !empty($quickViewLinks)}
		<td scope='col' class='listViewThS1' nowrap width='1%'>&nbsp;</td>
		{/if}
    </tr>

	{foreach name=rowIteration from=$data key=id item=rowData}
		{if $smarty.foreach.rowIteration.iteration is odd}
			{assign var='_bgColor' value=$bgColor[0]}
			{assign var='_rowColor' value=$rowColor[0]}
		{else}
			{assign var='_bgColor' value=$bgColor[1]}
			{assign var='_rowColor' value=$rowColor[1]}
		{/if}
		<tr height='20' onmouseover="setPointer(this, '{$id}', 'over', '{$_bgColor}', '{$bgHilite}', '');" onmouseout="setPointer(this, '{$rowData[$params.id]|default:$rowData.ID}', 'out', '{$_bgColor}', '{$bgHilite}', '');" onmousedown="setPointer(this, '{$id}', 'click', '{$_bgColor}', '{$bgHilite}', '');">
			{if $prerow}
			<td width='1%' class='{$_rowColor}S1' bgcolor='{$_bgColor}' nowrap>
					<input onclick='sListView.check_item(this, document.MassUpdate)' type='checkbox' class='checkbox' name='mass[]' value='{$rowData[$params.id]|default:$rowData.ID}'>
			</td>
			{/if}
			{counter start=0 name="colCounter" print=false assign="colCounter"}
			{foreach from=$displayColumns key=col item=params}
				<td scope='row' align='{$params.align|default:'left'}' valign=top class='{$_rowColor}S1' bgcolor='{$_bgColor}'><span sugar="sugar{$colCounter}b">
					{if $params.link && !$params.customCode}
						{* ITR 17057 DTam if displaying link for account or team then setting the correct record id *}
						<{$pageData.tag.$id[$params.ACLTag]|default:$pageData.tag.$id.MAIN} href='index.php?action={$params.action|default:'DetailView'}&module={if $params.dynamic_module}{$rowData[$params.dynamic_module]}{else}{$params.module|default:$pageData.bean.moduleDir}{/if}&record={if $params.module eq 'Accounts'}{$rowData.ACCOUNT_ID}{else}{$rowData[$params.id]|default:$rowData.ID}{/if}&offset={$pageData.offsets.current+$smarty.foreach.rowIteration.iteration}&stamp={$pageData.stamp}' class='listViewTdLinkS1'>{$rowData.$col}</{$pageData.tag.$id[$params.ACLTag]|default:$pageData.tag.$id.MAIN}>
					{elseif $params.customCode}
						{sugar_evalcolumn var=$params.customCode rowData=$rowData}
					{elseif $params.currency_format}
						{sugar_currency_format
							var=$rowData.$col
							round=$params.currency_format.round
							decimals=$params.currency_format.decimals
							symbol=$params.currency_format.symbol
						}
					{elseif $params.type == 'bool'}
							<input type='checkbox' disabled=disabled class='checkbox'
							{if !empty($rowData[$col])}
								checked=checked
							{/if}
							/>

					{else}
						{$rowData.$col}
					{/if}
				</span sugar='sugar{$colCounter}b'></td>
				{counter name="colCounter"}
			{/foreach}
	    	</tr>
	 	<tr><td colspan='20' class='listViewHRS1'></td></tr>
	{/foreach}
</table>
<br/>
