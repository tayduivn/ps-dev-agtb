<?php
require("custom/include/SugarCharts/cssParser.php");

class chartEngine {
	protected $ss;
	var $xmlFile;
	var $jsonFilename;
	var $chartId;
	var $width;
	var $height;
	var $chartType;

	function __construct() {
		$this->ss = new Sugar_Smarty();
	}
	
	function getChartResources() {
		return '
		<link type="text/css" href="'.getJSPath('custom/include/SugarCharts/css/base.css').'" rel="stylesheet" />
		<!--[if IE]><script language="javascript" type="text/javascript" src="'.getJSPath('custom/include/SugarCharts/js/Jit/Extras/excanvas.js').'"></script><![endif]-->
		<script language="javascript" type="text/javascript" src="'.getJSPath('custom/include/SugarCharts/js/Jit/jit.js').'"></script>
		<script language="javascript" type="text/javascript" src="'.getJSPath('custom/include/SugarCharts/js/customSugarCharts.js').'"></script>
		';
	}
	
	function tab($str, $depth){
		return str_repeat("\t", $depth) . $str;	
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
			"funnel chart 3D"
		);
		
		if(in_array($chartType,$charts)) {
			return true;
		} else {
			return false;
		}
		
	}
	function display() {
	
		$style = array();
		$chartConfig = array();
		$xmlStr = $this->processXML($this->xmlFile);
		$json = $this->buildJson($xmlStr);
		$this->saveJsonFile($json);
		$this->ss->assign("chartId", $this->chartId);
		$this->ss->assign("filename", $this->jsonFilename);

		
		$dimensions = $this->getChartDimensions($xmlStr);
		$this->ss->assign("width", $dimensions['width']);
		$this->ss->assign("height", $dimensions['height']);
		$css = $this->getChartCss();
		$config = $this->getConfigProperties();
		$style['gridLineColor'] = str_replace("0x","#",$config->gridLines);
		$style['font-family'] = $css[".barvaluelabels"]["font-family"];
		$style['color'] = $css[".barvaluelabels"]["color"];
		$this->ss->assign("css", $style);
		foreach($this->getChartConfigParams($xmlStr) as $key => $value) {
			$chartConfig[$key] = $value;
		}
		$this->ss->assign("config", $chartConfig);
		if($json == "No Data") {
			$this->ss->assign("nodata", "No Data");
		}

		return $this->ss->fetch('custom/include/SugarCharts/tpls/chart.tpl');	
	}
	

	function getDashletScript() {
		$style = array();
		$chartConfig = array();
		$this->ss->assign("chartId", $this->chartId);
		$this->ss->assign("filename", str_replace(".xml",".json",$this->xmlFile));
		$css = $this->getChartCss();
		$config = $this->getConfigProperties();
		$style['gridLineColor'] = str_replace("0x","#",$config->gridLines);
		$style['font-family'] = $css[".barvaluelabels"]["font-family"];
		$style['color'] = $css[".barvaluelabels"]["color"];
		$this->ss->assign("css", $style);
		$xmlStr = $this->processXML($this->xmlFile);
		foreach($this->getChartConfigParams($xmlStr) as $key => $value) {
			$chartConfig[$key] = $value;
		}
		$this->ss->assign("config", $chartConfig);
		
		return $this->ss->fetch('custom/include/SugarCharts/tpls/DashletGenericChartScript.tpl');
	}
	
	
	function chartArray($chartsArray) {
		
		$customChartsArray = array();
		$style = array();
		$chartConfig = array();
		foreach($chartsArray as $id => $data) {
			$customChartsArray[$id] = array();
			$customChartsArray[$id]['chartId'] = $id;
			$customChartsArray[$id]['filename'] = str_replace(".xml",".json",$data['xmlFile']);
			$customChartsArray[$id]['width'] = $data['width'];
			$customChartsArray[$id]['height'] = $data['height'];
			
			$config = $this->getConfigProperties();
			$css = $this->getChartCss();
			$style['gridLineColor'] = str_replace("0x","#",$config->gridLines);
			$style['font-family'] = $css[".barvaluelabels"]["font-family"];
			$style['color'] = $css[".barvaluelabels"]["color"];
			$customChartsArray[$id]['css'] = $style;
			$xmlStr = $this->processXML($data['xmlFile']);
			$xml = new SimpleXMLElement($xmlStr);
			$params = $this->getChartConfigParams($xmlStr);
			$customChartsArray[$id]['supported'] = ($this->isSupported($xml->properties->type)) ? "true" : "false";
			foreach($params as $key => $value) {
				$chartConfig[$key] = $value;
			}
			$customChartsArray[$id]['chartConfig'] = $chartConfig;
		}

		return $customChartsArray;
	}
	
	function getChartCss() {
		$cssParser = new cssparser;
		$path = SugarThemeRegistry::current()->getCSSURL('chart.css',false);
		$cssParser->Parse($path);
		
		$css = $cssParser->css;
		return $css;
	}
	
	function getChartConfigParams($xmlStr) {

		$xml = new SimpleXMLElement($xmlStr);
		
		$chartType = $xml->properties->type;
		if($chartType == "pie chart") {
			return array ("pieType" => "basic","tip" => "name","chartType" => "pieChart");
		} elseif($chartType == "funnel chart 3D") {
			return array ("funnelType" => "basic","tip" => "name","chartType" => "funnelChart");
		} elseif($chartType == "gauge chart") {
			return array ("gaugeType" => "basic","tip" => "name","chartType" => "gaugeChart");
		} elseif($chartType == "stacked group by chart") {
			return array ("orientation" => "vertical","barType" => "stacked","tip" => "name","chartType" => "barChart");
		} elseif($chartType == "group by chart") {
			return array("orientation" => "vertical", "barType" => "grouped", "tip" => "title","chartType" => "barChart");
		} elseif($chartType == "bar chart") {
			return array("orientation" => "vertical", "barType" => "basic", "tip" => "label","chartType" => "barChart");
		} elseif ($chartType == "horizontal group by chart") {
			return array("orientation" => "horizontal", "barType" => "stacked", "tip" => "name","chartType" => "barChart");
		} elseif ($chartType == "horizontal bar chart") {
			return array("orientation" => "horizontal","barType" => "basic","tip" => "label","chartType" => "barChart");
		} else {
			return array("orientation" => "vertical","barType" => "stacked","tip" => "name","chartType" => "barChart");
		}
	}
	function getChartDimensions($xmlStr) {
		if($this->getNumNodes($xmlStr) > 9 && $this->chartType != "pie chart") {
			if($this->chartType == "horizontal group by chart" || $this->chartType == "horizontal bar chart") {
				return array("width"=>$this->width, "height"=>($this->height * 2));
			} else {
				return array("width"=>($this->width * 2), "height"=>$this->height);
			}
		} else {
			return array("width"=>$this->width, "height"=>$this->height);
		}
	}
	
	function checkData($xmlstr) {
		$xml = new SimpleXMLElement($xmlstr);
		if(sizeof($xml->data->group) > 0) {
			return true;	
		} else {
			return false;	
		}
	}
	
	function getNumNodes($xmlstr) {
		$xml = new SimpleXMLElement($xmlstr);
		return sizeof($xml->data->group);
	}
	
  	function buildProperties($xmlstr) {
		$content = $this->tab("'properties': [\n",1);
		$properties = array();
		$xml = new SimpleXMLElement($xmlstr);
		foreach($xml->properties->children() as $property) {
			$properties[] = $this->tab("'".$property->getName()."':"."'".$property."'",2);
		}
		$content .= $this->tab("{\n",1);
		$content .= join(",\n",$properties)."\n";
		$content .= $this->tab("}\n",1);
		$content .= $this->tab("],\n",1);
		return $content;
	}
	
  	function buildLabelsBarChartStacked($xmlstr) {
		$content = $this->tab("'label': [\n",1);
		$labels = array();
		$xml = new SimpleXMLElement($xmlstr);
		foreach($xml->data->group[0]->subgroups->group as $group) {
			$labels[] = $this->tab("'".$group->title."'",2);
		}
		$content .= join(",\n",$labels)."\n";
		$content .= $this->tab("],\n",1);
		return $content;
	}

  	function buildLabelsBarChart($xmlstr) {
		$content = $this->tab("'label': [\n",1);
		$labels = array();
		$xml = new SimpleXMLElement($xmlstr);
		foreach($xml->data->group as $group) {
			$labels[] = $this->tab("'".$group->title."'",2);
		}
		$labelStr = join(",\n",$labels)."\n";
		$content .= $labelStr;
		$content .= $this->tab("],\n",1);
		return $content;
	}
	
	function buildDataBarChartStacked($xmlstr) {
		$content = $this->tab("'values': [\n",1);
		$data = array();
		$xml = new SimpleXMLElement($xmlstr);
		foreach($xml->data->group as $group) {
			$groupcontent = $this->tab("{\n",1);
			$groupcontent .= $this->tab("'label': '{$group->title}',\n",2);
			$groupcontent .= $this->tab("'gvalue': '{$group->value}',\n",2);
			$groupcontent .= $this->tab("'gvaluelabel': '{$group->label}',\n",2);
			$subgroupValues = array();
			$subgroupValueLabels = array();
			$subgroupLinks = array();
			foreach($group->subgroups->group as $subgroups) {
				$subgroupValues[] = $this->tab($subgroups->value,3);
				$subgroupValueLabels[] = $this->tab("'".$subgroups->label."'",3);
				$subgroupLinks[] = $this->tab("'".$subgroups->link."'",3);
			}
			$subgroupValuesStr = join(",\n",$subgroupValues)."\n";
			$subgroupValueLabelsStr = join(",\n",$subgroupValueLabels)."\n";
			$subgroupLinksStr = join(",\n",$subgroupLinks)."\n";
			
			$groupcontent .= $this->tab("'values': [\n".$subgroupValuesStr,2);
			$groupcontent .= $this->tab("],\n",2);
			$groupcontent .= $this->tab("'valuelabels': [\n".$subgroupValueLabelsStr,2);
			$groupcontent .= $this->tab("],\n",2);
			$groupcontent .= $this->tab("'links': [\n".$subgroupLinksStr,2);
			$groupcontent .= $this->tab("]\n",2);
			$groupcontent .= $this->tab("}",1);
			$data[] = $groupcontent;
		}
		$content .= join(",\n",$data)."\n";
		$content .= $this->tab("]",1);
		return $content;
	}
	
	function buildDataBarChartGrouped($xmlstr) {
		$content = $this->tab("'values': [\n",1);
		$data = array();
		$xml = new SimpleXMLElement($xmlstr);
		foreach($xml->data->group as $group) {
			$groupcontent = $this->tab("{\n",1);
			$groupcontent .= $this->tab("'label': '{$group->title}',\n",2);
			$groupcontent .= $this->tab("'gvalue': '{$group->value}',\n",2);
			$groupcontent .= $this->tab("'gvaluelabel': '{$group->label}',\n",2);
			$subgroupValues = array();
			$subgroupValueLabels = array();
			$subgroupLinks = array();
			$subgroupTitles = array();
			foreach($group->subgroups->group as $subgroups) {
				$subgroupValues[] = $this->tab($subgroups->value,3);
				$subgroupValueLabels[] = $this->tab("'".$subgroups->label."'",3);
				$subgroupLinks[] = $this->tab("'".$subgroups->link."'",3);
				$subgroupTitles[] = $this->tab("'".$subgroups->title."'",3);
			}
			$subgroupValuesStr = join(",\n",$subgroupValues)."\n";
			$subgroupValueLabelsStr = join(",\n",$subgroupValueLabels)."\n";
			$subgroupLinksStr = join(",\n",$subgroupLinks)."\n";
			$subgroupTitlesStr = join(",\n",$subgroupTitles)."\n";
			
			$groupcontent .= $this->tab("'values': [\n".$subgroupValuesStr,2);
			$groupcontent .= $this->tab("],\n",2);
			$groupcontent .= $this->tab("'valuelabels': [\n".$subgroupValueLabelsStr,2);
			$groupcontent .= $this->tab("],\n",2);
			$groupcontent .= $this->tab("'links': [\n".$subgroupLinksStr,2);
			$groupcontent .= $this->tab("],\n",2);
			$groupcontent .= $this->tab("'titles': [\n".$subgroupTitlesStr,2);
			$groupcontent .= $this->tab("]\n",2);
			$groupcontent .= $this->tab("}",1);
			$data[] = $groupcontent;
		}
		$content .= join(",\n",$data)."\n";
		$content .= $this->tab("]",1);
		return $content;
	}
	
	function buildDataBarChart($xmlstr) {
		$content = $this->tab("'values': [\n",1);
		$data = array();
		$xml = new SimpleXMLElement($xmlstr);
		$groupcontent = "";
		$groupcontentArr = array();

		foreach($xml->data->group as $group) {
		$groupcontent = $this->tab("{\n",1);
		$groupcontent .= $this->tab("'label': [\n",2);
		$groupcontent .= $this->tab("'{$group->title}'\n",3);
		$groupcontent .= $this->tab("],\n",2);
		$groupcontent .= $this->tab("'values': [\n",2);
		$groupcontent .= $this->tab("{$group->value}\n",3);
		$groupcontent .= $this->tab("],\n",2);
		if($group->label) {
			$groupcontent .= $this->tab("'valuelabels': [\n",2);
			$groupcontent .= $this->tab("{$group->label}\n",3);
			$groupcontent .= $this->tab("],\n",2);
		}
		$groupcontent .= $this->tab("'links': [\n",2);
		$groupcontent .= $this->tab("'{$group->link}'\n",3);
		$groupcontent .= $this->tab("]\n",2);
		$groupcontent .= $this->tab("}",1);
		$groupcontentArr[] = $groupcontent;
		}
		$content .= join(",\n",$groupcontentArr)."\n";
		$content .= $this->tab("]",1);
		return $content;
	}
	
	  function buildLabelsPieChart($xmlstr) {
		$content = $this->tab("'label': [\n",1);
		$labels = array();
		$xml = new SimpleXMLElement($xmlstr);
		foreach($xml->data->group as $group) {
			$labels[] = $this->tab("'".$group->title."'",2);
		}
		$labelStr = join(",\n",$labels)."\n";
		$content .= $labelStr;
		$content .= $this->tab("],\n",1);
		return $content;
	}
	
	
	function buildDataPieChart($xmlstr) {
		$content = $this->tab("'values': [\n",1);
		$data = array();
		$xml = new SimpleXMLElement($xmlstr);
		$groupcontent = "";
		$groupcontentArr = array();

		foreach($xml->data->group as $group) {
		$groupcontent = $this->tab("{\n",1);
		$groupcontent .= $this->tab("'label': [\n",2);
		$groupcontent .= $this->tab("'{$group->title}'\n",3);
		$groupcontent .= $this->tab("],\n",2);
		$groupcontent .= $this->tab("'values': [\n",2);
		$groupcontent .= $this->tab("{$group->value}\n",3);
		$groupcontent .= $this->tab("],\n",2);
		$groupcontent .= $this->tab("'valuelabels': [\n",2);
		$groupcontent .= $this->tab("'{$group->label}'\n",3);
		$groupcontent .= $this->tab("],\n",2);
		$groupcontent .= $this->tab("'links': [\n",2);
		$groupcontent .= $this->tab("'{$group->link}'\n",3);
		$groupcontent .= $this->tab("]\n",2);
		$groupcontent .= $this->tab("}",1);
		$groupcontentArr[] = $groupcontent;
		}

		
		$content .= join(",\n",$groupcontentArr)."\n";
		$content .= $this->tab("\n]",1);
		return $content;
	}
	
	function buildLabelsGaugeChart($xmlstr) {
		$content = $this->tab("'label': [\n",1);
		$labels = array();
		$xml = new SimpleXMLElement($xmlstr);
		foreach($xml->data->group as $group) {
			$labels[] = $this->tab("'".$group->title."'",2);
		}
		$labelStr = join(",\n",$labels)."\n";
		$content .= $labelStr;
		$content .= $this->tab("],\n",1);
		return $content;
	}
	
	function buildDataGaugeChart($xmlstr) {
		$content = $this->tab("'values': [\n",1);
		$data = array();
		$xml = new SimpleXMLElement($xmlstr);
		foreach($xml->data->group as $group) {
			$groupcontent = $this->tab("{\n",1);
			$groupcontent .= $this->tab("'label': '{$group->title}',\n",2);
			$groupcontent .= $this->tab("'gvalue': '{$group->value}',\n",2);
			$groupcontent .= $this->tab("'gvaluelabel': '{$group->label}',\n",2);
			$subgroupTitles = array();
			$subgroupValues = array();
			$subgroupValueLabels = array();
			$subgroupLinks = array();
			
			if(is_object($group->subgroups->group)) {
				foreach($group->subgroups->group as $subgroups) {
					$subgroupTitles[] = $this->tab("'".$subgroups->title."'",3);
					//$subgroupValues[] = $this->tab($subgroups->value,3);
					$subgroupValues[] = $subgroups->value;
					$subgroupValueLabels[] = $this->tab("'".$subgroups->label."'",3);
					$subgroupLinks[] = $this->tab("'".$subgroups->link."'",3);
				}
				$subgroupTitlesStr = join(",\n",$subgroupTitles)."\n";
				$subgroupValuesStr = join(",\n",$subgroupValues)."\n";
				$subgroupValueLabelsStr = join(",\n",$subgroupValueLabels)."\n";
				$subgroupLinksStr = join(",\n",$subgroupLinks)."\n";
				
				//$groupcontent .= $this->tab("'labels': [\n".$subgroupTitlesStr,2);
				//$groupcontent .= $this->tab("],\n",2);			
				$val = $this->tab($subgroupValues[1] - $subgroupValues[0],3)."\n";
				
				$groupcontent .= $this->tab("'values': [\n".$val,2);
				$groupcontent .= $this->tab("],\n",2);
				$groupcontent .= $this->tab("'valuelabels': [\n".$subgroupValueLabelsStr,2);
				$groupcontent .= $this->tab("]\n",2);
				//$groupcontent .= $this->tab("'links': [\n".$subgroupLinksStr,2);
				//$groupcontent .= $this->tab("]\n",2);

			}

				$groupcontent .= $this->tab("}",1);
				$data[] = $groupcontent;
				
		}
		
		$content .= join(",\n",$data)."\n";
				
		
		$content .= $this->tab("]",1);
		return $content;
	}
	
	
	function getConfigProperties() {
		$path = SugarThemeRegistry::current()->getImageURL('sugarColors.xml',false);
		
		if(!file_exists($path)) {
			$GLOBALS['log']->debug("Cannot open file ($path)");
		}
		$xmlstr = file_get_contents($path);
		$xml = new SimpleXMLElement($xmlstr);
		return $xml->charts;		
	}
	
	function buildChartColors() {

		$content = $this->tab("'color': [\n",1);
		$colorArr = array();
		$xml = $this->getConfigProperties();
		foreach($xml->chartElementColors->color as $color) {
			$colorArr[] = $this->tab("'".str_replace("0x","#",$color)."'",2);
		}
		$content .= join(",\n",$colorArr)."\n";
		$content .= $this->tab("],\n",1);
		
		return $content;
		
	}
	
	function buildJson($xmlstr){
		if($this->checkData($xmlstr)) {
			$content = "{\n";
			if ($this->chartType == "pie chart" || $this->chartType == "funnel chart 3D") {
				$content .= $this->buildProperties($xmlstr);
				$content .= $this->buildLabelsPieChart($xmlstr);
				$content .= $this->buildChartColors();
				$content .= $this->buildDataPieChart($xmlstr);
			}
			elseif ($this->chartType == "gauge chart") {
				$content .= $this->buildProperties($xmlstr);
				$content .= $this->buildLabelsGaugeChart($xmlstr);
				$content .= $this->buildChartColors();
				$content .= $this->buildDataGaugeChart($xmlstr);
			}
			elseif ($this->chartType == "horizontal bar chart" || $this->chartType == "bar chart") {
				$content .= $this->buildProperties($xmlstr);
				$content .= $this->buildLabelsBarChart($xmlstr);
				$content .= $this->buildChartColors();
				$content .= $this->buildDataBarChart($xmlstr);
			}			
			elseif ($this->chartType == "group by chart") {
				$content .= $this->buildProperties($xmlstr);
				$content .= $this->buildLabelsBarChartStacked($xmlstr);
				$content .= $this->buildChartColors();
				$content .= $this->buildDataBarChartGrouped($xmlstr);
			} else {
				$content .= $this->buildProperties($xmlstr);
				$content .= $this->buildLabelsBarChartStacked($xmlstr);
				$content .= $this->buildChartColors();
				$content .= $this->buildDataBarChartStacked($xmlstr);
			}
			$content .= "\n}";
			return $content;
		} else {
			return "No Data";	
		}
	}
	
	
	function saveJsonFile($jsonContents) {
		
		$this->jsonFilename = str_replace(".xml",".json",$this->xmlFile);
		//$jsonContents = mb_convert_encoding($jsonContents, 'UTF-16LE', 'UTF-8'); 
		
		// open file
		if (!$fh = sugar_fopen($this->jsonFilename, 'w')) {
			$GLOBALS['log']->debug("Cannot open file ($this->jsonFilename)");
			return;
		}
		
		// write the contents to the file
		if (fwrite($fh,$jsonContents) === FALSE) {
			$GLOBALS['log']->debug("Cannot write to file ($this->jsonFilename)");
			return false;
		}
	
		$GLOBALS['log']->debug("Success, wrote ($jsonContents) to file ($this->jsonFilename)");
	
		fclose($fh);
		return true;
	}

	function processXML($xmlFile) {
		
		if(!file_exists($xmlFile)) {
			$GLOBALS['log']->debug("Cannot open file ($xmlFile)");
		}
		
		$pattern = array();
		$replacement = array();
		$content = file_get_contents($xmlFile);
		$content = mb_convert_encoding($content, 'UTF-8','UTF-16LE' );
		$pattern[] = '/\<link\>([a-zA-Z0-9#?&%.;\[\]\/=+_-\s]+)\<\/link\>/e';
		$replacement[] = "'<link>'.urlencode(\"$1\").'</link>'";
		$pattern[] = '/NULL/e';
		$replacement[] = "";
		return preg_replace($pattern,$replacement, $content);
	}

	
}

?>