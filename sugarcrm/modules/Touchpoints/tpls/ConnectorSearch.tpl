<form name="SearchForm" method="POST" id="SearchForm">
<div id="div_search_form">
{include file="modules/Touchpoints/tpls/ConnectorSearchForm.tpl"}
</div>
</form>

<form name="ConnectorStep1" method="POST">
<input type="hidden" name="action" value="Step2">
<input type="hidden" name="module" value="Connectors">
<input type="hidden" name="record" value="{$RECORD}">
<table width="100%" cellpadding="0" cellspacing="0" border="0" class="h3Row">
<tr>
<td nowrap><h3>{$mod.LBL_RESULT_LIST}</h3></td>
<td width='100%'><img height='1' width='1' src='include/images/blank.gif' alt=''></td>
</tr>
</table>

<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tr>
	<td>
		<table border='0' cellpadding='0' cellspacing='0' width='100%'>
				<tr>
					<td nowrap>
<input title="{$mod.LBL_MERGE}" accessKey="{$APP.LBL_NEXT_BUTTON_KEY}" class="button" type="button" name="button" value="{$mod.LBL_MERGE}" onclick="run_merge();">
					</td>
				</tr>				
		</table>	
	</td>
</tr>
<tr>
</table>

<br>

<table cellspacing="0" cellpadding="0" border="0" width="100%" class="tabDetailViewDF">
<tr>
<td>
{$TABS}
</td>
</tr>
</table>

<table cellspacing="0" cellpadding="0" border="0" width="100%">
<tr><td>
 {counter assign=source_count start=0 print=0} 
 {foreach name=connectors from=$SOURCES key=name item=source}   
 {counter assign=source_count}
 	{if $source_count == 1}
	<div id='div_{$source}' style='height:300px; overflow:auto; display:block'></div>
	{else}
	<div id='div_{$source}' style='height:300px; overflow:auto; display:none'></div>
	{/if}
	<script type='text/javascript'>
		var sourceTab = new SourceTab('{$source}');
		_sourceArray[{$source_count}-1] = sourceTab;
		{if $source_count == 1}
	    sourceTab.refreshData(true);
		{/if}
	</script>
	<div id='div_search_form_data_{$source}' style='display:none'></div>
{/foreach}
</td></tr>
</table>
</form>