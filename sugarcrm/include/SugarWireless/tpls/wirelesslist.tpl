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
<hr />
<!-- ListView Data -->
{if $SUBPANEL_LIST_VIEW}
	<div class="sectitle">
	{$BEAN->name} <small>[ <a class="back_link" href="index.php?module={$MODULE}&action=wirelessdetail&record={$BEAN->id}">{sugar_translate label='LBL_BACK' module=''}</a> ]</small>
	</div>
	<div class="subpanel_sec">
	{sugar_translate label='LBL_RELATED' module=''} {$SUBPANEL_MODULE}<br />
	</div>
	<ul class="sec">
	{foreach from=$DATA item="record" name="recordlist"}
	<li class="{if $smarty.foreach.recordlist.index % 2 == 0}odd{else}even{/if}">
        <a href="index.php?module={$record->module_dir}&action=wirelessdetail&record={$record->id}">{$record->name}</a>
    </li>
	{/foreach}
	</ul>
{else}
	<div class="sectitle">{sugar_translate label='LBL_SEARCH_RESULTS' module=''}{if $SAVED_SEARCH_NAME} - {$SAVED_SEARCH_NAME}{/if}</div>
	
	<table class="sec">
	
	<tr>
		{foreach from=$displayColumns key=colHeader item=params}
			<td scope='col' width='{$params.width}%' nowrap="nowrap">
				{sugar_translate label=$params.label module=$pageData.bean.moduleDir}
			</td>
		{/foreach}
	</tr>	
	
	{foreach from=$DATA item="rowData" name="recordlist"}
	<tr>

		{foreach from=$displayColumns key=col item=params}				
		<td class="{if $smarty.foreach.recordlist.index % 2 == 0}odd{else}even{/if}">
				{if $params.link && !$params.customCode}
                    {capture assign=linkModule}{if $params.dynamic_module}{$rowData[$params.dynamic_module]}{else}{$MODULE}{/if}{/capture}
                    {capture assign=linkRecord}{$rowData[$params.id]|default:$rowData.ID}{/capture}
                    <a href="index.php?module={$linkModule}&action=wirelessdetail&record={$linkRecord}">{$rowData.$col}</a>
                {elseif $params.customCode} 
					{sugar_evalcolumn_old var=$params.customCode rowData=$rowData}
				{elseif $params.currency_format}
                    {**
                     * Need to refactor this wireless list fields to use the
                     * SugarFields enabling customization levels per field.
                     * Currency fields shouldn't be defined using name fields
                     * like "_USD", but rather a parameter telling what is the
                     * related currency for that field.
                     *
                     * @see SugarFieldCurrency::getListViewSmarty
                     * @see include/SugarFields/Fields/Currency/ListView.tpl
                     *}
                    {if stripos(strtoupper($col), '_USD')}
                        {sugar_currency_format var=$rowData.$col}
                    {elseif !empty($rowData.CURRENCY_ID)}
                        {sugar_currency_format var=$rowData.$col
                        currency_id=$rowData.CURRENCY_ID
                        }
                    {elseif !empty($rowData.currency_id)}
                        {sugar_currency_format var=$rowData.$col
                        currency_id=$rowData.currency_id
                        }
                    {else}
                        {* empty currency id *}
                        {sugar_currency_format var=$rowData.$col}
                    {/if}
				{elseif $params.type == 'bool'}
						<input type='checkbox' disabled=disabled class='checkbox'
						{if !empty($rowData[$col])}
							checked=checked
						{/if}
						/>
				{* //BEGIN SUGARCRM flav=pro ONLY*}
				{elseif $params.type == 'teamset'}
					{$rowData.$col}
				{* //END SUGARCRM flav=pro ONLY*}
				{elseif $params.type == 'multienum'}
					{if !empty($rowData.$col)} 
						{counter name="oCount" assign="oCount" start=0}
						{assign var="vals" value='^,^'|explode:$rowData.$col}
						{foreach from=$vals item=item}
							{counter name="oCount"}
							{sugar_translate label=$params.options select=$item}{if $oCount !=  count($vals)},{/if} 
						{/foreach}	
					{/if}
				{else}	
					{$rowData.$col|default:"&nbsp;"}
				{/if}
		</td>
		{/foreach}
	</tr>
	{/foreach}
    </table>
	
	<div class="nav_sec" align="right">
	{if $PAGEDATA.offsets.prev != -1}<small><a href="{$PAGEDATA.urls.prevPage}" class="nav">{$navStrings.previous}</a>&nbsp;</small>{/if}
	{if $PAGEDATA.offsets.lastOffsetOnPage == 0}0{else}{$PAGEDATA.offsets.current+1}{/if} - {$PAGEDATA.offsets.lastOffsetOnPage} {$navStrings.of} {if $PAGEDATA.offsets.totalCounted}{$PAGEDATA.offsets.total}{else}{$PAGEDATA.offsets.total}{if $PAGEDATA.offsets.lastOffsetOnPage != $PAGEDATA.offsets.total}+{/if}{/if}
	{if $PAGEDATA.offsets.next != -1}<small>&nbsp;<a href="{$PAGEDATA.urls.nextPage}" class="nav">{$navStrings.next}</a></small>{/if}
	</div>
	<div class="sectitle">{sugar_translate label='LBL_SEARCH' module=''} {$MODULE_NAME} {$LITERAL_MODULE}</div>
	{$WL_SAVED_SEARCH_FORM}
	<!--  Search Def Searches -->
	{$WL_SEARCH_FORM}	
{/if}
