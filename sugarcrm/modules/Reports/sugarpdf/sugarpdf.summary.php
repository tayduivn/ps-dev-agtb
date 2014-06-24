<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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

require_once('modules/Reports/sugarpdf/sugarpdf.reports.php');
require_once('modules/Reports/templates/templates_chart.php');
require_once('include/SugarCharts/SugarChartFactory.php');
require_once('include/SugarCharts/ChartDisplay.php');

class ReportsSugarpdfSummary extends ReportsSugarpdfReports
{
    function display(){
        global $locale;
        
        
        //add chart
        if (isset($_REQUEST['id']) && $_REQUEST['id'] != false) {
	    	$this->bean->is_saved_report = true;
	    }
        // fixing bug #47260: Print PDF Reports
        // if chart_type is 'none' we don't need do add any image to pdf
        if ($this->bean->chart_type != 'none')
        {
            $chartDisplay = new ChartDisplay();
            $xmlFile = $chartDisplay->get_cache_file_name($this->bean);

            $sugarChart = SugarChartFactory::getInstance();
            if($sugarChart->supports_image_export) {
                $imageFile = $sugarChart->get_image_cache_file_name($xmlFile,".".$sugarChart->image_export_type);
                // check image size is not '0'
                if(file_exists($imageFile) && getimagesize($imageFile) > 0)
                {
                    $this->AddPage();
                    list($width, $height) = getimagesize($imageFile); 
                    $imageHeight = ($height >= $width) ? $this->getPageHeight()*.7 : "";
                    $imageWidth = ($width >= $width) ? $this->getPageWidth()*.9 : "";
                    $this->Image($imageFile,$this->GetX(),$this->GetY(),$imageWidth,$imageHeight,"","","N",false,300,"", false,false,0,true);

                    if($sugarChart->print_html_legend_pdf)
                    {
                        $legend = $sugarChart->buildHTMLLegend($xmlFile);
        //		    	$this->Write(12,$legend);
                        $this->writeHTML($legend,true,false,false,true,"");
                    }
                }
            }
        }
	    
        //Create new page           
        $this->AddPage();
        
        $this->bean->run_summary_query();
        $item = array();
        $header_row = $this->bean->get_summary_header_row();
        $count = 0;
    
        if(count($this->bean->report_def['summary_columns']) == 0) {
            $item[$count]['']='';
            $count++;
        }
        if(count($this->bean->report_def['summary_columns']) > 0) {
            while($row = $this->bean->get_summary_next_row()) {
                for($i= 0 ; $i < sizeof($header_row); $i++) {
                    $label = $header_row[$i];
                    $value = '';
                    if (isset($row['cells'][$i])) {
                        $value = $row['cells'][$i];
                    }
                    $item[$count][$label] = $value;
    
                }
                $count++;
            }
        }
        
        $this->writeCellTable($item, $this->options);
        $this->Ln1();
    
        $this->bean->clear_results();
        
        if($this->bean->has_summary_columns()) $this->bean->run_total_query();
    
        $total_header_row = $this->bean->get_total_header_row();
        $total_row = $this->bean->get_summary_total_row();
        $item = array();
        $count = 0;
        for($j=0; $j < sizeof($total_header_row); $j++) {
            $label = $total_header_row[$j];
            $item[$count][$label] = $total_row['cells'][$j];
        }
        
        $this->writeCellTable($item, $this->options);
        
	   
	    
    }

    
}


