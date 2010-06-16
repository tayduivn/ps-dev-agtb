<?php
//FILE SUGARCRM flav=pro || flav=sales ONLY
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

require_once('include/SugarCharts/SugarChart.php');

class SugarChartReports extends SugarChart {
	
	private $processed_report_keys = array();
	
	public function __construct() {
		parent::__construct();		
	}

	function calculateReportGroupTotal($dataset){
		$total = 0;				
		foreach ($dataset as $value){
			$total += $value['numerical_value'];
		}
		
		return $total;
	}	
	
	function processReportData($dataset, $level=1, $first=false){
		$data = '';
		
		// rearrange $dataset to get the correct order for the first row
		if ($first){
			$temp_dataset = array();
			foreach ($this->super_set as $key){
				$temp_dataset[$key] = (isset($dataset[$key])) ? $dataset[$key] : array();
			}
			$dataset = $temp_dataset;			
		}
		
		foreach ($dataset as $key=>$value){
			if ($first && empty($value)){
				$data .= $this->processDataGroup(4, $key, 'NULL', '', '');
			}			
			else if (array_key_exists('numerical_value', $dataset)){
				$data .= $this->processDataGroup($level, $dataset['group_base_text'], $dataset['numerical_value'], $dataset['numerical_value'], '');
				array_push($this->processed_report_keys, $dataset['group_base_text']);
				return $data;
			}
			else{
				$data .= $this->processReportData($value, $level+1);
			}
		}
		
		return $data;
	}
	
	function processReportGroup($dataset){
		$super_set = array();

        foreach($dataset as $groupBy => $groups){
            $prev_super_set = $super_set;
            if (count($groups) > count($super_set)){
	            $super_set = array_keys($groups);
                foreach($prev_super_set as $prev_group){
                    if (!in_array($prev_group, $groups)){
                        array_push($super_set, $prev_group);
                    }       
                }       
            }       
            else{ 
                foreach($groups as $group => $groupData){
                    if (!in_array($group, $super_set)){ 
                        array_push($super_set, $group);
                    }       
                }       
            }       
        }     
        $super_set = array_unique($super_set);

		return $super_set;
	}
	
	function xmlDataReportSingleValue(){
		$data = '';		
		foreach ($this->data_set as $key => $dataset){
			$total = $this->calculateReportGroupTotal($dataset);
			$this->checkYAxis($total);						

			$data .= $this->tab('<group>', 2);
			$data .= $this->tab('<title>' . $key . '</title>', 3);
			$data .= $this->tab('<subgroups>', 3);
			$data .= $this->tab('<group>',4);
			$data .= $this->tab('<title>' . $total . '</title>',5);
			$data .= $this->tab('<value>' . $total . '</value>',5);
			$data .= $this->tab('<label>' . $key . '</label>',5);
			$data .= $this->tab('<link></link>',5);
			$data .= $this->tab('</group>',4);
			$data .= $this->tab('</subgroups>', 3);				
			$data .= $this->tab('</group>', 2);			
		}
		return $data;
	}
	
	function xmlDataReportChart(){
		$data = '';
		// correctly process the first row
		$first = true;	
		foreach ($this->data_set as $key => $dataset){
			
			$total = $this->calculateReportGroupTotal($dataset);
			$this->checkYAxis($total);
			
			$data .= $this->tab('<group>', 2);
			$data .= $this->tab('<title>' . $key . '</title>', 3);
			$data .= $this->tab('<value>' . $total . '</value>', 3);
			$data .= $this->tab('<label>' . $total . '</label>', 3);				
			$data .= $this->tab('<subgroups>', 3);
			
			if ((isset($dataset[$total]) && $total != $dataset[$total]['numerical_value']) || !array_key_exists($key, $dataset)){
					$data .= $this->processReportData($dataset, 4, $first);
			}

			if (!$first){											
				$not_processed = array_diff($this->super_set, $this->processed_report_keys);
				$processed_diff_count = count($this->super_set) - count($not_processed);

				if ($processed_diff_count != 0){
					foreach ($not_processed as $title){
						$data .= $this->processDataGroup(4, $title, 'NULL', '', '');
					}
				}
			}
			$data .= $this->tab('</subgroups>', 3);				
			$data .= $this->tab('</group>', 2);				
			$this->processed_report_keys = array();
			// we're done with the first row!
			$first = false;
		}
		return $data;		
	}
	
	public function processXmlData(){
		$data = '';
		
		$this->super_set = $this->processReportGroup($this->data_set);
		$single_value = false;

		foreach ($this->data_set as $key => $dataset){
			if ((isset($dataset[$key]) && count($this->data_set[$key]) == 1)){
				$single_value = true;
			}
			else{
				$single_value = false;
			}
		}
		if ($this->chart_properties['type'] == 'line chart' && $single_value){
			$data .= $this->xmlDataReportSingleValue();
		}
		else{
			$data .= $this->xmlDataReportChart();
		}
		
		return $data;		
	}	
		
	/**
     * wrapper function to return the html code containing the chart in a div
	 * 
     * @param 	string $name 	name of the div
	 *			string $xmlFile	location of the XML file
	 *			string $style	optional additional styles for the div
     * @return	string returns the html code through smarty
     */					
	function display($name, $xmlFile, $width='320', $height='480', $reportChartDivStyle, $resize=false){
		
		
		// generate strings for chart if it does not exist
		global $current_language, $theme, $sugar_config,$app_strings;
		
		$chartStringsXML = $GLOBALS['sugar_config']['tmp_dir'].'chart_strings.' . $current_language .'.lang.xml';
		if (!file_exists($chartStringsXML)){
			$this->generateChartStrings($chartStringsXML);
		}
							
		$this->ss->assign("chartName", $name);
		$this->ss->assign("chartXMLFile", $xmlFile);
		$this->ss->assign("chartStringsXML", $chartStringsXML);
		$this->ss->assign("style", $reportChartDivStyle);
		
		// chart styles and color definitions
		$this->ss->assign("chartStyleCSS", SugarThemeRegistry::current()->getCSSURL('chart.css'));
		$this->ss->assign("chartColorsXML", SugarThemeRegistry::current()->getImageURL('sugarColors.xml'));
		
		$this->ss->assign("width", $width);
		$this->ss->assign("height", $height);
		
		$this->ss->assign("resize", $resize);
		$this->ss->assign("app_strings", $app_strings);				
		return $this->ss->fetch('include/SugarCharts/tpls/chart.tpl');
	}
	
} // end class def