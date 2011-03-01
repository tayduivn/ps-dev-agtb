<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('include/pdf/class.ezpdf.php');
require_once('modules/Reports/Report.php');

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *(i) the "Powered by SugarCRM" logo and
 *(ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright(C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

global $paperWidth;
$paperWidth = 730; // cn: bug 3627 - default paper width for PDFs.  will change if paper size changes

//////////////////////////////////////////////
// TEMPLATE:
//////////////////////////////////////////////

function template_summary_combo_pdf(&$reporter, $stream=true) {
	global $app_list_strings, $report_modules;
	global $app_strings, $locale;
	//disable paging so we get all results in one pass
	$reporter->enable_paging = false;
	$cols = count($reporter->report_def['display_columns']);
	$pdf = preprocess_pdf($cols);
	//$pdf = preprocess_pdf();
	$reporter->run_summary_combo_query();
	$reporter->_load_currency();

	$header_row = $reporter->get_summary_header_row();
	$columns_row = $reporter->get_header_row();
	if($reporter->name == "untitled") {
		$options = pdf_table_options($app_list_strings['moduleList'][$reporter->module]);
	} else {
		$options = pdf_table_options($locale->translateCharset($reporter->name, 'UTF-8', $locale->getExportCharset()));
	}

	$options_no_count = $options;
	$options_no_count['showRowCount']=0;
	$pdf->ezSetDy(20);

	while(($row = $reporter->get_summary_next_row()) != 0) {
		$item = array();
		$count = 0;

		for($i=0; $i < $reporter->current_summary_row_count; $i++) {
			if(($column_row = $reporter->get_next_row('result', 'display_columns', false, true)) != 0) {
		   		for($j=0; $j < sizeof($columns_row); $j++) {
					$label = $locale->translateCharset($columns_row[$j], 'UTF-8', $locale->getExportCharset());
		   			$item[$count][$label] = $column_row['cells'][$j];
		   		}
		   		$count++;
			} else {
				break;
			}
		}

		$pdf->ezSetDy(-40);
		$pdf->ezTable($item,'' ,'' ,$options);
		$item = array();
		$count = 0;
		for($j=0; $j < sizeof($row['cells']); $j++) {
			if($j > count($header_row) - 1)
				$label = $header_row[count($header_row) - 1];
			else
				$label = $locale->translateCharset($header_row[$j], 'UTF-8', $locale->getExportCharset());
			if(preg_match('/type.*=.*checkbox/Uis', $row['cells'][$j])) { // parse out checkboxes
				if(preg_match('/checked/i', $row['cells'][$j])) $row['cells'][$j] = $app_list_strings['dom_switch_bool']['on'];
				else $row['cells'][$j] = $app_list_strings['dom_switch_bool']['off'];
			}
			$value = html_entity_decode($row['cells'][$j], ENT_QUOTES, 'UTF-8');
			$item[$count][$label] = $locale->translateCharset($value, 'UTF-8', $locale->getExportCharset());

		}

		$pdf->ezSetDy(-20);
		$pdf->ezTable($item,'' ,'' ,$options_no_count);
	}


	$reporter->clear_results();
	if ( $reporter->has_summary_columns()) {
		$reporter->run_total_query();
		$total_row = $reporter->get_summary_total_row();
		$item = array();
		$count = 0;
		for($j=0; $j < sizeof($header_row); $j++) {
			$label = $header_row[$j];
			$value = html_entity_decode($total_row['cells'][$j], ENT_QUOTES, 'UTF-8');
			$item[$count][$label] = $locale->translateCharset($value, 'UTF-8', $locale->getExportCharset());
		}
		$pdf->ezSetDy(-20);
		$pdf->ezTable($item,'' ,'' ,$options);
	}

	return postprocess_pdf($pdf,$reporter->name, $stream);
}



function strip_html_pdf($text) {
	$text = strip_tags($text);
	return html_entity_decode($text);
}


function template_summary_pdf(&$reporter, $stream = true) {
	global $app_list_strings, $report_modules, $locale;
	//$pdf = preprocess_pdf();

	$reporter->run_summary_query();
	$cols = count($reporter->report_def['display_columns']);
	$pdf = preprocess_pdf($cols);

	$item = array();
	$header_row = $reporter->get_summary_header_row();
	$count = 0;

	if(count($reporter->report_def['summary_columns']) == 0) {
		$item[$count]['']='';
		$count++;
	}
	if(count($reporter->report_def['summary_columns']) > 0) {
		while($row = $reporter->get_summary_next_row()) {
			for($i= 0 ; $i < sizeof($header_row); $i++) {
				$label = $header_row[$i];
				$value = '';
				if(!empty($row['cells'][$i])) {
					$value = $row['cells'][$i];
				}
				$item[$count][$label] = $locale->translateCharset($value, 'UTF-8', $locale->getExportCharset());

			}
			$count++;
		}
	}

	if($reporter->name == "untitled") {
		$options = pdf_table_options($app_list_strings['moduleList'][$reporter->module]);
	} else {
		$options = pdf_table_options($locale->translateCharset($reporter->name, 'UTF-8', $locale->getExportCharset()));
	}
	$pdf->ezSetDy(-20);
	$pdf->ezTable($item,'' ,'' ,$options);

	$reporter->clear_results();
    $run_total_query = false; // check if one of the summary columns is a count, then run total otherwise do not run total
    foreach($reporter->report_def['summary_columns'] as $c => $col) {
        if($col['name'] == 'count') $run_total_query = true;
    }
	if($run_total_query) $reporter->run_total_query();

	$total_header_row = $reporter->get_total_header_row();
	$total_row = $reporter->get_summary_total_row();
	$item = array();
	$count = 0;
	for($j=0; $j < sizeof($total_header_row); $j++) {
		$label = $total_header_row[$j];
		$item[$count][$label] = $total_row['cells'][$j];
	}
	$pdf->ezSetDy(-20);
	$pdf->ezTable($item,'' ,'' ,$options);

	return postprocess_pdf($pdf,$reporter->name, $stream);
}

function checkMissingField($reporter, $focus, $dis_col,$stream) {
    if(empty($focus->field_name_map[$dis_col['name']])){
		$options = pdf_table_options('Error');
		$pdf = preprocess_pdf();
		$pdf->addText(40, 500, 15, "SQL error, ".$focus->object_name."->".$dis_col['name']." doesn't exist!");//it is hardcoded, because currently pdf doesn't support i18.
		return postprocess_pdf($pdf,$reporter->name, $stream);
	}
}

/**
 * @return stream or string
 */
function template_handle_pdf(&$reporter, $stream = true) {
    ini_set('zlib.output_compression', 'Off');
	$reporter->enable_paging = false;
	$reporter->plain_text_output = true;

    if(!empty($reporter->report_def['display_columns'])) {//bug 26175 bug 28805
        foreach($reporter->report_def['display_columns'] as $dis_col) {//only check the custom field to avoid potential problem.
	        if(!empty($dis_col['name']) && strpos($dis_col['name'], '_c') && !empty($dis_col['table_key'])){
		       	if($dis_col['table_key'] == 'self'){
		       		checkMissingField($reporter, $reporter->focus, $dis_col, $stream);
		       	} else {
		        	$rel_modules =  explode(':',$dis_col['table_key']);
			       	unset($rel_modules[0]);       	 
			       	$rel_handler = $reporter->focus->call_relationship_handler("module_dir", true);
			        $rel_handler->set_rel_vardef_fields($rel_modules[1]);
			        $rel_handler->build_info(false);
			        unset($rel_modules[1]);
			        
			       	foreach($rel_modules as $rel_module){        
				        $rel_handler = $rel_handler->rel1_bean->call_relationship_handler("module_dir", true);
				        $rel_handler->set_rel_vardef_fields($rel_module);
			            $rel_handler->build_info(false);
			       	}
			       	checkMissingField($reporter, $rel_handler->rel1_bean, $dis_col, $stream);
		       	}
	       }      
       }
    }

	if($reporter->report_type == 'summary' && !empty($reporter->report_def['summary_columns'])) {
		if($reporter->show_columns
			&& !empty($reporter->report_def['display_columns'])
			&& !empty($reporter->report_def['group_defs'])) {
			return template_summary_combo_pdf($reporter, $stream);
		} elseif($reporter->show_columns
			&& !empty($reporter->report_def['display_columns'])
			&& empty($reporter->report_def['group_defs'])) {
			return template_detail_and_total_pdf($reporter,$stream);
		} elseif(!empty($reporter->report_def['group_defs'])) {
			return template_summary_pdf($reporter, $stream);
		} else {
			return template_total_pdf($reporter,$stream);
		}
	} elseif(!empty($reporter->report_def['display_columns'])) {
		return template_listview_pdf($reporter, $stream);
	}
}

//////////////////////////////////////////////
// TEMPLATE:
//////////////////////////////////////////////
function template_total_pdf(&$reporter, $stream = true) {
	global $report_modules, $app_list_strings, $locale;
	global $mod_strings;
	//$pdf = preprocess_pdf();

	$cols = count($reporter->report_def['display_columns']);
	$pdf = preprocess_pdf($cols);

	if($reporter->name == "untitled") {
		$options = pdf_table_options($app_list_strings['moduleList'][$reporter->module]);
	} else {
		$options = pdf_table_options($reporter->name);
	}
	$pdf->ezSetDy(-20);
	$pdf->ezTable($item,'' ,'' ,$options);

	$reporter->clear_results();
	$reporter->run_total_query();

	$total_header_row = $reporter->get_total_header_row(true);
	$total_row = $reporter->get_summary_total_row(true);

	$item = array();
	$count = 0;
	for($j=0; $j < sizeof($total_header_row); $j++) {
	  $label = $total_header_row[$j];
	  $value =  html_entity_decode($total_row['cells'][$j], ENT_QUOTES, 'UTF-8');
	  $item[$count][$label] = $locale->translateCharset($value, 'UTF-8', $locale->getExportCharset());
	}
	$pdf->ezSetDy(-20);
	$pdf->ezTable($item,'' ,'' ,$options);

	return postprocess_pdf($pdf,$reporter->name, $stream);
}


//////////////////////////////////////////////
// TEMPLATE:
//////////////////////////////////////////////
function template_detail_and_total_pdf(&$reporter, $stream = true) {
	global $report_modules, $app_list_strings;
    global $mod_strings, $locale;

	$cols = count($reporter->report_def['display_columns']);
	$pdf = preprocess_pdf($cols);
	$reporter->run_query();
	$reporter->_load_currency();

	$item = array();
	$header_row = $reporter->get_header_row('display_columns',false, true);
	$count = 0;

	while($row = $reporter->get_next_row('result', 'display_columns', false, true)) {
		for($i= 0 ; $i < sizeof($header_row); $i++) {
		    $label = $locale->translateCharset($header_row[$i], 'UTF-8', $locale->getExportCharset());
		    $value = '';
		    if(!empty($row['cells'][$i])) {
		      $value = $row['cells'][$i];
		    }
		    $item[$count][$label] = $locale->translateCharset($value, 'UTF-8', $locale->getExportCharset());
		}
		$count++;
	}

	if($reporter->name == "untitled") {
		$options = pdf_table_options($app_list_strings['moduleList'][$reporter->module]);
	} else {
		$options = pdf_table_options($locale->translateCharset($reporter->name, $locale->getExportCharset()));
	}
	$pdf->ezSetDy(-20);
	$pdf->ezTable($item,'' ,'' ,$options);

	$reporter->clear_results();
	$reporter->run_total_query();
	$total_header_row = $reporter->get_total_header_row();

	$total_row = $reporter->get_summary_total_row();
	$item = array();
	$count = 0;
	for($j=0; $j < sizeof($total_header_row); $j++) {
	  $label = $total_header_row[$j];
	  $value =  html_entity_decode($total_row['cells'][$j], ENT_QUOTES, 'UTF-8');
	  $item[$count][$label] = $locale->translateCharset($value, 'UTF-8', $locale->getExportCharset());
	}
	$pdf->ezSetDy(-20);
	$pdf->ezTable($item,'' ,'' ,$options);



	return postprocess_pdf($pdf,$reporter->name, $stream);
}





//////////////////////////////////////////////
// TEMPLATE:
//////////////////////////////////////////////
function template_listview_pdf(&$reporter, $stream = true) {
	global $report_modules, $app_list_strings;
	global $mod_strings, $locale;
	//$pdf = preprocess_pdf();
	$reporter->run_query();
	$reporter->_load_currency();
	$cols = count($reporter->report_def['display_columns']);
	$pdf = preprocess_pdf($cols);

	$item = array();
	$header_row = $reporter->get_header_row();
	$count = 0;

	while($row = $reporter->get_next_row('result', 'display_columns', false, true)) {
		for($i= 0 ; $i < sizeof($header_row); $i++) {
			$label = $locale->translateCharset($header_row[$i], 'UTF-8', $locale->getExportCharset());
			$value = '';
			if(!empty($row['cells'][$i])) {
				$value = $row['cells'][$i];
			}
			$item[$count][$label] = $locale->translateCharset($value, 'UTF-8', $locale->getExportCharset());
		}
		$count++;
	}

	if($reporter->name == "untitled") {
		$options = pdf_table_options($app_list_strings['moduleList'][$reporter->module]);
	} else {
		$options = pdf_table_options($locale->translateCharset($reporter->name, 'UTF-8', $locale->getExportCharset()));
	}
	$pdf->ezSetDy(-20);
	$pdf->ezTable($item,'' ,'' ,$options);
	return postprocess_pdf($pdf,$reporter->name, $stream);
}

function pdf_table_options($title="SugarCRM Report") {
	global $paperWidth;

	$options = array();
	$options['width'] = $paperWidth;
	$options['shaded'] = 0;
	$options['titleFontSize'] = 10;
	$options['fontSize'] = 8;
	$options['shadeHeadings'] = 1;
	$options['headCol'] = array(.3,.3,.3);
	$options['headTextCol'] = array(1,1,1);
	$options['xPos'] = 25;
	$options['xOrientation'] = 'right';
	$options['showHeadings'] = 1;
	$options['showLines'] = 0;
	$options['shaded'] = 1;
	$options['showRowCount']=1;
	$options['display_footer'] = 1;
	$options['footer'] = array('Generated by SugarCRM', 'http://www.sugarcrm.com');
	$options['header'] = $title;
	$options['display_header'] = 1;
	return $options;
}

/**
 * cn: bug 3627 - too many columns push columns off the page when outputting a
 * PDF file.  This calculates the appropriate page size (and indent/paperwidth)
 * based on a loose, best-guess cols-to-width calculation
 * @param int	number of columns in the report
 * @return string	paper size
 */
function getPaperSize($cols) {
	global $paperWidth;

	if($cols > 7 && $cols < 10) { // 8-9 cols Legal Size
		$paperWidth = 960;
		return 'LEGAL';
	} elseif($cols >= 10) { // 10+ cols Tabloid Size
		$paperWidth = 1170;
		return 'TABLOID';
	}
	return 'LETTER'; // default for reports
}

function preprocess_pdf($cols=0) {
	$font = "include/fonts/Helvetica";

	$paperSize = getPaperSize($cols);

	$pdf = new Cezpdf($paperSize, 'landscape');
	$pdf->selectFont($font);

	$pdf->ezStartPageNumbers(720, 30, 10, 'right', '', 1);

	// adds line to top and bottom of pages
	$all = $pdf->openObject();
	$pdf->saveState();

	$pdf->restoreState();
	$pdf->closeObject();
	$pdf->addObject($all,'all');

	return $pdf;
}

function postprocess_pdf(&$pdf,$reportname,$stream) {
	global $current_user;

	//-------begin printing of page footer--------------
	$all = $pdf->openObject();
	$pdf->saveState();

	$pdf->y = 42;
	$print_date = date("m/d/Y", time());
	$fOptions['justification'] = 'left';
	$date_time_footer = $print_date;
	$pdf->ezText($date_time_footer, 10, $fOptions);

	$pdf->restoreState();
	$pdf->closeObject();
	$pdf->addObject($all,'all');
	//-------end printing of page footer--------------


	$filenamestamp = '';
	if(isset($current_user)) {
		$filenamestamp .= '_'.$current_user->user_name;
	}
	$filenamestamp .= '_'.date(translate('LBL_PDF_TIMESTAMP', 'Reports'), time());
	$cr = array(' ',"\r", "\n","/");
	$filename = str_replace($cr, '_', $reportname.$filenamestamp.'.pdf');
	if(isset($_SERVER['HTTP_USER_AGENT']) && preg_match("/MSIE/", $_SERVER['HTTP_USER_AGENT'])) {
		$filename = urlencode($filename);
	} 
	$stream_options =	array(	'Content-Disposition' => $filename,
								'compress'            => 1,
						);
	if($stream) {
		$pdf->ezStream($stream_options);
	} else {
		$fp = sugar_fopen($GLOBALS['sugar_config']['cache_dir'].'pdf/'.$filename,'w');
		fwrite($fp, $pdf->output());
		fclose($fp);

		return $GLOBALS['sugar_config']['cache_dir'].'pdf/'.$filename;
	}

	return $filename;
}
