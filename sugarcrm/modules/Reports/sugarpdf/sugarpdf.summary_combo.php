<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


require_once('modules/Reports/sugarpdf/sugarpdf.reports.php');

class ReportsSugarpdfSummary_combo extends ReportsSugarpdfReports
{
    function display(){
        global $app_list_strings, $locale;
        
        $this->AddPage();
        
        //disable paging so we get all results in one pass
        $this->bean->enable_paging = false;
        $cols = count($this->bean->report_def['display_columns']);
        $this->bean->run_summary_combo_query();
    
        $header_row = $this->bean->get_summary_header_row();
        $columns_row = $this->bean->get_header_row();
    
        while(($row = $this->bean->get_summary_next_row()) != 0) {
            $item = array();
            $count = 0;
    
            for($i=0; $i < $this->bean->current_summary_row_count; $i++) {
                if(($column_row = $this->bean->get_next_row('result', 'display_columns', false, true)) != 0) {
                    for($j=0; $j < sizeof($columns_row); $j++) {
                        $label = $columns_row[$j];
                        $item[$count][$label] = $column_row['cells'][$j];
                    }
                    $count++;
                } else {
                    break;
                }
            }
            $this->writeCellTable($item, $this->options);
            $this->Ln1();
            
            $item = array();
            $count = 0;
            for($j=0; $j < sizeof($row['cells']); $j++) {
                if($j > count($header_row) - 1)
                    $label = $header_row[count($header_row) - 1];
                else
                    $label = $header_row[$j];
                if(preg_match('/type.*=.*checkbox/Uis', $row['cells'][$j])) { // parse out checkboxes
                    if(preg_match('/checked/i', $row['cells'][$j])) $row['cells'][$j] = $app_list_strings['dom_switch_bool']['on'];
                    else $row['cells'][$j] = $app_list_strings['dom_switch_bool']['off'];
                }
                $value = $row['cells'][$j];
                $item[$count][$label] = $value;
    
            }
            $this->writeCellTable($item, $this->options);
            $this->Ln1();
        }
    
    
        $this->bean->clear_results();
        if ( $this->bean->has_summary_columns()) {
            $this->bean->run_total_query();
            $total_row = $this->bean->get_summary_total_row();
            $item = array();
            $count = 0;
            for($j=0; $j < sizeof($header_row); $j++) {
                $label = $header_row[$j];
                $value = $total_row['cells'][$j];
                $item[$count][$label] = $value;
            }
            $this->writeCellTable($item, $this->options);
        }
    }
    
}

