{*
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2004-2006 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
*}
<div id="{$chartName}_div" style="width:{$width};height:{$height}px;z-index:80;{$style}" class="chartDiv">
 	<object type="application/x-shockwave-flash" data="include/SugarCharts/swf/chart.swf?inputFile={$chartXMLFile}&swfLocation=include/SugarCharts/swf/&inputColorScheme={$chartColorsXML}&inputStyleSheet={$chartStyleCSS}&inputLanguage={$chartStringsXML}" width="100%" height="100%">
		<param name="allowScriptAccess" value="sameDomain"/>
		<param name="movie" value="include/SugarCharts/swf/chart.swf?inputFile={$chartXMLFile}&swfLocation=include/SugarCharts/swf/&inputColorScheme={$chartColorsXML}&inputStyleSheet={$chartStyleCSS}&inputLanguage={$chartStringsXML}"/>
		<param name="menu" value="false"/>
		<param name="quality" value="high"/>
		<param name="wmode" value="transparent" />
		<p>{$app_strings.LBL_NO_FLASH_PLAYER}</p>
	</object>
</div>

<script type="text/javascript">
	if (typeof SUGAR == 'undefined' || typeof SUGAR.mySugar == 'undefined') {ldelim}
		// no op
		loadChartForReports();
	{rdelim} else {ldelim}
		SUGAR.mySugar.addToChartsArray('{$chartName}', '{$chartXMLFile}', '{$width}', '{$height}', '{$chartStyleCSS}', '{$chartColorsXML}', '{$chartStringsXML}');
	{rdelim}
	
	var loadDone=0;
	function loadChartForReports() {ldelim}
		//only allow 5 tries
		if (loadDone > 5) 
			return;
		if(typeof(loadChartSWF) == 'function'){ldelim}
			//if the function exists, call the function and set the flag
			loadChartSWF('{$chartName}', '{$chartXMLFile}', '{$width}', '{$height}', '{$chartStyleCSS}', '{$chartColorsXML}', '{$chartStringsXML}');
			loadDone = 8;
		{rdelim}else{ldelim}
			//the function has not been loaded yet, so increaste the count and call the current function again
			loadDone = loadDone+1;
			setTimeout("loadChartForReports()",500);
		{rdelim}		
	{rdelim}
</script>
