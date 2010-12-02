{if !$nodata}
<script type="text/javascript">
	var css = new Array();
	var chartConfig = new Array();
	{foreach from=$css key=selector item=property}
	css["{$selector}"] = '{$property}';
	{/foreach}
	{foreach from=$config key=name item=value}
	chartConfig["{$name}"] = '{$value}';
	{/foreach}
	if (typeof SUGAR == 'undefined' || typeof SUGAR.mySugar == 'undefined') {ldelim}
		// no op
		loadChartForReports();
	{rdelim} else {ldelim}
		SUGAR.mySugar.customCharts.addToCustomChartsArray('{$chartId}','{$filename}',css,chartConfig,activePage);
	{rdelim}
	
	function loadChartForReports() {ldelim}

	
		SUGAR.mySugar.customCharts.loadCustomChart('{$chartId}','{$filename}',css,chartConfig);
	{rdelim}
</script>



    <div id="{$chartId}" class="chartCanvas" style="width: {$width}; height: {$height}px;"></div>    
	
	
	<div id="legend{$chartId}" class="legend">

	</div>

{else}

{$nodata}
{/if}