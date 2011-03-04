<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to
 * *******************************************************************************/
/*********************************************************************************
 * $Id$
 * Description:
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc. All Rights
 * Reserved. Contributor(s): ______________________________________..
 *********************************************************************************/

require_once("include/SugarCharts/SugarChart.php");

class SugarFlash extends SugarChart {


	public function __construct() {
		parent::__construct();
	}

	function isSupported($chartType) {
		$charts = array(
			"stacked group by chart",
			"group by chart",
			"bar chart",
			"horizontal group by chart",
			"horizontal",
			"horizontal bar chart",
			"pie chart",
			"gauge chart",
			"funnel chart 3D",
			"line chart",
		);

		if(in_array($chartType,$charts)) {
			return true;
		} else {
			return false;
		}

	}


	function getChartResources() {
		return
		'<script type="text/javascript" src="' . getJSPath('include/javascript/swfobject.js') . '"></script>
		<script language="javascript" type="text/javascript" src="'.getJSPath('include/SugarCharts/SugarFlash/js/sugarCharts.js').'"></script>
		';
	}


	function getMySugarChartResources() {
		return '
		<script language="javascript" type="text/javascript" src="'.getJSPath('include/SugarCharts/SugarFlash/js/mySugarCharts.js').'"></script>
		';
	}

	/**
     * wrapper function to return the html code containing the chart in a div
	 *
     * @param 	string $name 	name of the div
	 *			string $xmlFile	location of the XML file
	 *			string $style	optional additional styles for the div
     * @return	string returns the html code through smarty
     */
	function display($name, $xmlFile, $width='320', $height='480', $resize=false){


		parent::display($name, $xmlFile, $width, $height, $resize);
		$this->ss->assign("chartName", $name);
		$this->ss->assign("chartXMLFile", $xmlFile);
		$this->ss->assign("chartStringsXML", $this->chartStringsXML);

		// chart styles and color definitions
		$this->ss->assign("chartStyleCSS", SugarThemeRegistry::current()->getCSSURL('chart.css'));
		$this->ss->assign("chartColorsXML", SugarThemeRegistry::current()->getImageURL('sugarColors.xml'));

		$this->ss->assign("width", $width);
		$this->ss->assign("height", $height);

		$this->ss->assign("resize", $resize);
		$this->ss->assign("app_strings", $this->app_strings);

		return $this->ss->fetch('include/SugarCharts/SugarFlash/tpls/chart.tpl');

	}

	function getDashletScript($id,$xmlFile="") {


		global $sugar_config, $current_user, $current_language;
		$this->id = $id;
		$xmlFile = (!$xmlFile) ? sugar_cached("xml/{$current_user->id}_{$this->id}.xml") : $xmlFile;
		$chartStringsXML = sugar_cached("xml/chart_strings.$current_language.lang.xml");

		$this->ss->assign('chartName', $this->id);
        $this->ss->assign('chartXMLFile', $xmlFile);

        $this->ss->assign('chartStyleCSS', SugarThemeRegistry::current()->getCSSURL('chart.css'));
        $this->ss->assign('chartColorsXML', SugarThemeRegistry::current()->getImageURL('sugarColors.xml'));
        $this->ss->assign('chartStringsXML', $chartStringsXML);


		return $this->ss->fetch('include/SugarCharts/SugarFlash/tpls/DashletGenericChartScript.tpl');
	}

} // end class def

?>