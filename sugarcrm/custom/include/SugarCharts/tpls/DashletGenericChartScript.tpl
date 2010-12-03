<script>
	var customChart = true;
	var css = new Array();
	var chartConfig = new Array();
	{foreach from=$css key=selector item=property}
	css["{$selector}"] = '{$property}';
	{/foreach}
	{foreach from=$config key=name item=value}
	chartConfig["{$name}"] = '{$value}';
	{/foreach}
	
    SUGAR.mySugar.customCharts.addToCustomChartsArray('{$chartId}','{$filename}',css,chartConfig,activePage);
</script>