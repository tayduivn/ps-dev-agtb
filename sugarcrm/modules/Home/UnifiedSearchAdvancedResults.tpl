{*

/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

// $Id: UnifiedSearchAdvancedResults.tpl 42215 2008-11-26 20:35:04Z jmertic $

*}


{if $overlib}
	<script type="text/javascript" src="include/javascript/sugar_grp_overlib.js"></script>
	<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
{/if}

<table cellpadding="0" cellspacing="0" width="100%" border="0" class="list view">
	<tr class='pagination'>
		<td colspan="{$colCount}" align="right">
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tr>
					<td align="left">{$exportLink}{$mergeLink}{$selectedObjectsSpan}&nbsp;</td>
					<td align="right" nowrap="nowrap">
						{if $pageData.urls.startPage}
							<a href="{$pageData.urls.startPage}" {if $prerow}onclick="javascript:return sListView.save_checks(0, '{$moduleString}')"{/if} ><img src="{sugar_getimagepath file="start.gif"}" alt="Start" align="absmiddle" border="0" height="10" width="11">&nbsp;Start</a>&nbsp;&nbsp;
						{else}
							<img src="{sugar_getimagepath file="start_off.gif"}" alt="Start" align="absmiddle" border="0" height="10" width="11">&nbsp;Start&nbsp;&nbsp;
						{/if}
						{if $pageData.urls.prevPage}
							<a href="{$pageData.urls.prevPage}" {if $prerow}onclick="javascript:return sListView.save_checks(0, '{$moduleString}')"{/if} ><img src="{sugar_getimagepath file="previous.gif"}" alt="Previous" align="absmiddle" border="0" height="10" width="6">&nbsp;Previous</a>&nbsp;&nbsp;
						{else}
							<img src="{sugar_getimagepath file="previous_off.gif"}" alt="Previous" align="absmiddle" border="0" height="10" width="6">&nbsp;Previous&nbsp;&nbsp;
						{/if}
							<span class="pageNumbers">({$pageData.offsets.current+1} - {$pageData.offsets.next} of {if $pageData.offsets.totalCounted}{$pageData.offsets.total}{else}{$rowCount+1}}+{/if})</span>&nbsp;&nbsp;
						{if $pageData.urls.nextPage}
							<a href="{$pageData.urls.nextPage}" {if $prerow}onclick="javascript:return sListView.save_checks(40, '{$moduleString}')"{/if} >Next&nbsp;<img src="{sugar_getimagepath file="next.gif"}" alt="Next" align="absmiddle" border="0" height="10" width="6"></a>&nbsp;&nbsp;
						{else}
							&nbsp;&nbsp;Next&nbsp;<img src="{sugar_getimagepath file="next_off.gif"}" alt="Next" align="absmiddle" border="0" height="10" width="6">
						{/if}
						{if $pageData.urls.endPage}
							<a href="{$pageData.urls.endPage}" {if $prerow}onclick="javascript:return sListView.save_checks(980, '{$moduleString}')"{/if} >End&nbsp;<img src="{sugar_getimagepath file="end.gif"}" alt="End" align="absmiddle" border="0" height="10" width="11"></a></td>
						{else}
							&nbsp;&nbsp;Next&nbsp;<img src="{sugar_getimagepath file="next_off.gif"}" alt="Next" align="absmiddle" border="0" height="10" width="6">
						{/if}
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr height="20">
		{if $prerow}
			<td scope="col"  NOWRAP>{$checkall}</td>
		{/if}
		{foreach from=$displayColumns key=colHeader item=params}
			<td scope="col" width="{$params.width}" align="{$params.align}"  nowrap>
				<slot><a href="{$pageData.urls.orderBy}{$params.orderBy}" class="listViewThLinkS1">{$params.label}&nbsp;<img src="{smarty_function_sugar_getimagepath file='arrow.gif'}" alt="Sort" align="absmiddle" border="0"></a></slot>
			</td>
		{/foreach}
	</tr>
		
	{counter start=0 name=rowCounter print=false}
	{foreach from=$data key=id item=rowData}
		{if $rowCounter is even}
			{assign var="_rowColor" value=$rowColor[0]}
		{else}
			{assign var="_rowColor" value=$rowColor[1]}
		{/if}
		<tr height="20" class="{$_rowColor}S1">
			{if $prerow}
				<td><input onclick='sListView.check_item(this, document.MassUpdate)' type='checkbox' class='checkbox' name='mass[]' value='{$id}'></td>
			{/if}
			{foreach from=$displayColumns key=col item=params}
				<td scope='row' align="{$params.align|default:"left"}" valign="top"><slot>
					{if $params.link}
						{if $params.customCode}
							{sugar_evalcolumn_old var=$params.customCode rowData=$rowData}
						{else}
							<{$pageData.tag.$id.MAIN} href="index.php?action={$params.action|default:"DetailView"}&module={$params.module|default:$pageData.bean.moduleDir}&record={$rowData[$params.id]|default:$id}&offset={$pageData.offsets.current}&stamp={$pageData.stamp}" >{$rowData.$col}</{$pageData.tag.$id.MAIN}>
						{/if}
					{else}
						{$rowData.$col}
					{/if}
				</slot></td>
			{/foreach}
	    	</tr>
	 	
	 	{counter print=false}
	{/foreach}
	<tr class='pagination'>
		<td colspan="{$colCount}" align="right">
			<table border="0" cellpadding="0" cellspacing="0" width="100%">
				<tr>
					<td align="left">{$exportLink}{$mergeLink}{$selectedObjectsSpan}&nbsp;</td>
					<td align="right" nowrap="nowrap">
						{if $pageData.urls.startPage}
							<a href="{$pageData.urls.startPage}" onclick="javascript:return sListView.save_checks(0, '{$moduleString}')" ><img src="{sugar_getimagepath file="start.gif"}" alt="Start" align="absmiddle" border="0" height="10" width="11">&nbsp;Start</a>&nbsp;&nbsp;
						{else}
							<img src="{sugar_getimagepath file="start_off.gif"}" alt="Start" align="absmiddle" border="0" height="10" width="11">&nbsp;Start&nbsp;&nbsp;
						{/if}
						{if $pageData.urls.prevPage}
							<a href="{$pageData.urls.prevPage}" onclick="javascript:return sListView.save_checks(0, '{$moduleString}')" ><img src="{sugar_getimagepath file="previous.gif"}" alt="Previous" align="absmiddle" border="0" height="10" width="6">&nbsp;Previous</a>&nbsp;&nbsp;
						{else}
							<img src="{sugar_getimagepath file="previous_off.gif"}" alt="Previous" align="absmiddle" border="0" height="10" width="6">&nbsp;Previous&nbsp;&nbsp;
						{/if}
							<span class="pageNumbers">({$pageData.offsets.current+1} - {$pageData.offsets.next} of {if $pageData.offsets.totalCounted}{$pageData.offsets.end}{else}{$rowCount+1}}+{/if})</span>&nbsp;&nbsp;
						{if $pageData.urls.nextPage}
							<a href="{$pageData.urls.nextPage}" onclick="javascript:return sListView.save_checks(40, '{$moduleString}')" >Next&nbsp;<img src="{sugar_getimagepath file="next.gif"}" alt="Next" align="absmiddle" border="0" height="10" width="6"></a>&nbsp;&nbsp;
						{else}
							&nbsp;&nbsp;Next&nbsp;<img src="{sugar_getimagepath file="next_off.gif"}" alt="Next" align="absmiddle" border="0" height="10" width="6">
						{/if}
						{if $pageData.urls.endPage}
							<a href="{$pageData.urls.endPage}" onclick="javascript:return sListView.save_checks(980, '{$moduleString}')" >End&nbsp;<img src="{sugar_getimagepath file="end.gif"}" alt="End" align="absmiddle" border="0" height="10" width="11"></a></td>
						{else}
							&nbsp;&nbsp;Next&nbsp;<img src="{sugar_getimagepath file="next_off.gif"}" alt="Next" align="absmiddle" border="0" height="10" width="6">
						{/if}
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>