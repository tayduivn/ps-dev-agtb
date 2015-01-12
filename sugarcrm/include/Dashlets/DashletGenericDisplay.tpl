{*
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
*}

{assign var="alt_start" value=$navStrings.start}
{assign var="alt_next" value=$navStrings.next}
{assign var="alt_prev" value=$navStrings.previous}
{assign var="alt_end" value=$navStrings.end}

<table cellpadding='0' cellspacing='0' width='100%' border='0' class='list view'>
    <tr class="pagination" role=”presentation”>
        <td colspan='{$colCount+1}' align='right'>
            <table border='0' cellpadding='0' cellspacing='0' width='100%'>
                <tr>
                    <td align='left'>&nbsp;</td>
                    <td align='right' nowrap='nowrap'>
                        {if $pageData.urls.startPage}
                            <!--<a href='#' onclick='return SUGAR.mySugar.retrieveDashlet("{$dashletId}", "{$pageData.urls.startPage}")' >{sugar_getimage name="start" ext=".png" width="14" height="13" alt=$navStrings.start other_attributes='align="absmiddle" border="0" '}&nbsp;{$navStrings.start}</a>&nbsp;-->
							<button title='{$navStrings.start}' class='button' onclick='return SUGAR.mySugar.retrieveDashlet("{$dashletId}", "{$pageData.urls.startPage}")'>
								{sugar_getimage name="start.png" attr='align="absmiddle" border="0" ' alt="$alt_start"}
							</button>

                        {else}
                            <!--{sugar_getimage name="start_off" ext=".png" alt=$navStrings.start other_attributes='align="absmiddle" border="0" '}&nbsp;{$navStrings.start}&nbsp;&nbsp;-->
							<button title='{$navStrings.start}' class='button' disabled>
								{sugar_getimage name="start_off.png" attr='align="absmiddle" border="0" '}
							</button>

                        {/if}
                        {if $pageData.urls.prevPage}
                            <!--<a href='#' onclick='return SUGAR.mySugar.retrieveDashlet("{$dashletId}", "{$pageData.urls.prevPage}")' >{sugar_getimage name="previous" ext=".png" width="8" height="13" alt=$navStrings.previous other_attributes='align="absmiddle" border="0" '}&nbsp;{$navStrings.previous}</a>&nbsp;-->
							<button title='{$navStrings.previous}' class='button' onclick='return SUGAR.mySugar.retrieveDashlet("{$dashletId}", "{$pageData.urls.prevPage}")'>
								{sugar_getimage name="previous.png" attr='align="absmiddle" border="0" ' alt="$alt_prev"}
							</button>

                        {else}
                            <!--{sugar_getimage name="previous_off" ext=".png" width="8" height="13" alt=$navStrings.previous other_attributes='align="absmiddle" border="0" '}&nbsp;{$navStrings.previous}&nbsp;-->
							<button class='button' disabled title='{$navStrings.previous}'>
								{sugar_getimage name="previous_off.png" attr='align="absmiddle" border="0" '}
							</button>
                        {/if}
                            <span class='pageNumbers'>({if $pageData.offsets.lastOffsetOnPage == 0}0{else}{$pageData.offsets.current+1}{/if} - {$pageData.offsets.lastOffsetOnPage} {$navStrings.of} {if $pageData.offsets.totalCounted}{$pageData.offsets.total}{else}{$pageData.offsets.total}{if $pageData.offsets.lastOffsetOnPage != $pageData.offsets.total}+{/if}{/if})</span>
                        {if $pageData.urls.nextPage}
                            <!--&nbsp;<a href='#' onclick='return SUGAR.mySugar.retrieveDashlet("{$dashletId}", "{$pageData.urls.nextPage}")' >{$navStrings.next}&nbsp;{sugar_getimage name="next" ext=".png" width="8" height="13" alt=$navStrings.next other_attributes='align="absmiddle" border="0" '}</a>&nbsp;-->
							<button title='{$navStrings.next}' class='button' onclick='return SUGAR.mySugar.retrieveDashlet("{$dashletId}", "{$pageData.urls.nextPage}")'>
								{sugar_getimage name="next.png" attr='align="absmiddle" border="0" ' alt="$alt_next"}
							</button>

                        {else}
                           <!-- &nbsp;{$navStrings.next}&nbsp;{sugar_getimage name="next_off" ext=".png" width="8" height="13" alt=$navStrings.next other_attributes='align="absmiddle" border="0" '}-->
							<button class='button' title='{$navStrings.next}' disabled>
								{sugar_getimage name="next_off.png" attr='align="absmiddle" border="0" '}
							</button>

                        {/if}
						{if $pageData.urls.endPage  && $pageData.offsets.total != $pageData.offsets.lastOffsetOnPage}
                            <!--<a href='#' onclick='return SUGAR.mySugar.retrieveDashlet("{$dashletId}", "{$pageData.urls.endPage}")' >{$navStrings.end}&nbsp;{sugar_getimage name="end" ext=".png" width="14" height="13" alt=$navStrings.end other_attributes='align="absmiddle" border="0" '}</a></td>-->
							<button title='{$navStrings.end}' class='button' onclick='return SUGAR.mySugar.retrieveDashlet("{$dashletId}", "{$pageData.urls.endPage}")'>
								{sugar_getimage name="end.png" attr='align="absmiddle" border="0" ' alt="$alt_end"}							
							</button>

						{elseif !$pageData.offsets.totalCounted || $pageData.offsets.total == $pageData.offsets.lastOffsetOnPage}
                            <!--&nbsp;{$navStrings.end}&nbsp;{sugar_getimage name="end_off" ext=".png" width="14" height="13" alt=$navStrings.end other_attributes='align="absmiddle" border="0" '}-->
							<button class='button' disabled title='{$navStrings.end}'>
							 	{sugar_getimage name="end_off.png" attr='align="absmiddle" border="0" '}
							</button>

                        {/if}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr height='20'>
        {foreach from=$displayColumns key=colHeader item=params}
	        <th scope='col' width='{$params.width}%'>
				<div style='white-space: normal;'width='100%' align='{$params.align|default:'left'}'>
                {if $params.sortable|default:true} 
	                <a href='#' onclick='return SUGAR.mySugar.retrieveDashlet("{$dashletId}", "{$pageData.urls.orderBy}{$params.orderBy|default:$colHeader|lower}&sugar_body_only=1&id={$dashletId}")' class='listViewThLinkS1' title="{$arrowAlt}">{sugar_translate label=$params.label module=$pageData.bean.moduleDir}</a>&nbsp;&nbsp;
	                {if $params.orderBy|default:$colHeader|lower == $pageData.ordering.orderBy}
	                    {if $pageData.ordering.sortOrder == 'ASC'}
                            {capture assign="imageName"}arrow_down.{$arrowExt}{/capture}
                            {capture assign="alt_sort"}{sugar_translate label='LBL_ALT_SORT_DESC'}{/capture}
	                        {sugar_getimage name=$imageName width=$arrowWidth height=$arrowHeight attr='align="absmiddle" border="0" ' alt="$alt_sort"}
	                    {else}
                            {capture assign="imageName"}arrow_up.{$arrowExt}{/capture}
                            {capture assign="alt_sort"}{sugar_translate label='LBL_ALT_SORT_ASC'}{/capture}
	                        {sugar_getimage name=$imageName width=$arrowWidth height=$arrowHeight attr='align="absmiddle" border="0" ' alt="$alt_sort"}
	                    {/if}
	                {else}
                        {capture assign="imageName"}arrow.{$arrowExt}{/capture}
                        {capture assign="alt_sort"}{sugar_translate label='LBL_ALT_SORT'}{/capture}
	                    {sugar_getimage name=$imageName width=$arrowWidth height=$arrowHeight attr='align="absmiddle" border="0" ' alt="$alt_sort"}
	                {/if}
	           {else}
                    {if !isset($params.noHeader) || $params.noHeader == false}
	           		  {sugar_translate label=$params.label module=$pageData.bean.moduleDir}
                    {/if}
	           {/if}
			   </div>
            </th>
        {/foreach}
		{if !empty($quickViewLinks)}
		<td  class='td_alt' nowrap="nowrap" width='1%'>&nbsp;</td>
		{/if}
    </tr>
        
	{foreach name=rowIteration from=$data key=id item=rowData}
		{if $smarty.foreach.rowIteration.iteration is odd}
			{assign var='_rowColor' value=$rowColor[0]}
		{else}
			{assign var='_rowColor' value=$rowColor[1]}
		{/if}
		<tr height='20' class='{$_rowColor}S1'>
			{if $prerow}
			<td width='1%' nowrap='nowrap'>
					<input onclick='sListView.check_item(this, document.MassUpdate)' type='checkbox' class='checkbox' name='mass[]' value='{$rowData[$params.id]|default:$rowData.ID}'>
			</td>
			{/if}
			{counter start=0 name="colCounter" print=false assign="colCounter"}
			{foreach from=$displayColumns key=col item=params}
			    {strip}
				<td scope='row' align='{$params.align|default:'left'}' valign="top" {if ($params.type == 'teamset')}class="nowrap"{/if}>
					{if $col == 'NAME' || $params.bold}<b>{/if}
				    {if $params.link && !$params.customCode}
						<{$pageData.tag.$id[$params.ACLTag]|default:$pageData.tag.$id.MAIN} href='index.php?action={$params.action|default:'DetailView'}&module={if $params.dynamic_module}{$rowData[$params.dynamic_module]}{else}{$params.module|default:$pageData.bean.moduleDir}{/if}&record={$rowData[$params.id]|default:$rowData.ID}&offset={$pageData.offsets.current+$smarty.foreach.rowIteration.iteration}&stamp={$pageData.stamp}'>
					{/if}
					{if $params.customCode}
						{sugar_evalcolumn_old var=$params.customCode rowData=$rowData}
					{else}
                       {sugar_field parentFieldArray=$rowData vardef=$params displayType=ListView field=$col}
					{/if}
					{if empty($rowData.$col) && empty($params.customCode)}&nbsp;{/if}
					{if $params.link && !$params.customCode}
						</{$pageData.tag.$id[$params.ACLTag]|default:$pageData.tag.$id.MAIN}>
                    {/if}
                    {if $col == 'NAME' || $params.bold}</b>{/if}
				</td>
				{/strip}
				{counter name="colCounter"}
			{/foreach}
			{if !empty($quickViewLinks)}
			<td width='1%' class='{$_rowColor}S1' bgcolor='{$_bgColor}' nowrap>
				{if $pageData.rowAccess[$id].edit}
                    {capture name='tmp1' assign='alt_edit'}{sugar_translate label="LNK_EDIT"}{/capture}
                    {capture name='tmp1' assign='alt_view'}{sugar_translate label="LBL_VIEWINLINE"}{/capture}
					<a
                        {* //BEGIN SUGARCRM flav=pro ONLY *}
                        class="quickEdit"  data-dashlet-id='{$dashletId}'  data-record='{$rowData.ID}' data-module='{$pageData.bean.moduleDir}'
                        {* //END SUGARCRM flav=pro ONLY *}
                        title='{$editLinkString}' href='index.php?action=EditView&module={$pageData.bean.moduleDir}&record={$rowData.ID}&offset={$pageData.offsets.current+$smarty.foreach.rowIteration.iteration}&stamp={$pageData.stamp}&return_module=Home'>{sugar_getimage name="edit_inline.png" attr='border="0" ' alt="$alt_edit"}</a>
				{/if}
				{if $pageData.access.view}
					<a title='{$viewLinkString}' href='index.php?action=DetailView&module={$params.module|default:$pageData.bean.moduleDir}&record={$rowData[$params.parent_id]|default:$rowData.ID}&offset={$pageData.offsets.current+$smarty.foreach.rowIteration.iteration}&stamp={$pageData.stamp}&return_module=Home' title="{sugar_translate label="LBL_VIEW_INLINE"}>{sugar_getimage name="view_inline.png" attr='border="0" ' alt="$alt_view"}</a>
				{/if}
			</td>
			{/if}
	    	</tr>
	{foreachelse}
	<tr height='20' class='{$rowColor[0]}S1'>
	    <td colspan="{$colCount}">
	        <em>{$APP.LBL_NO_DATA}</em>
	    </td>
	</tr>
	{/foreach}
</table>
