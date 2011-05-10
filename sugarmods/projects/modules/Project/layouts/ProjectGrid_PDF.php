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
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: Standard.php 16493 2006-08-28 22:18:01 +0000 (Mon, 28 Aug 2006) chris $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

require_once('XTemplate/xtpl.php');
require_once('data/Tracker.php');
require_once('modules/Project/Project.php');
require_once('modules/ProjectTask/ProjectTask.php');
require_once('include/pdf/class.ezpdf.php');

global $mod_strings, $app_strings, $app_list_strings;
global $current_user, $currentModule, $action, $record, $focus, $locale;
//turn off all error reporting so that PHP warnings don't munge the PDF code
error_reporting(E_ALL);
set_time_limit(1800);
$GLOBALS['log']->info("Project Grid layout view");
// cn: bug 8587 handle strings for export
$mod_strings		= $locale->translateStringPack($mod_strings, $locale->getExportCharset());
$app_strings		= $locale->translateStringPack($app_strings, $locale->getExportCharset());
$app_list_strings	= $locale->translateStringPack($app_list_strings, $locale->getExportCharset());

$font = "include/fonts/Helvetica";
$pdf = new Cezpdf('LETTER', 'landscape');
// wp: must substitute chr(2) for the euro character symbol because it is not part of the
// standard ISO set. see [ http://www.ros.co.nz/pdf/faq.php#16 ]
$euro_diff = array(2=>'Euro');
$pdf->selectFont($font, array('differences'=>$euro_diff));
//$pdf->ezStartPageNumbers(400, 10, 10, 'right', '', 1);
//$pdf->ezStartPageNumbers(720, 30, 10, 'right', '', 1);

//todo: calltranslateCharsetfor all that's being printed
//$val = $locale->translateCharset($val, 'UTF-8', $locale->getExportCharset());

//adds line to top and bottom of pages
$all = $pdf->openObject();
$pdf->saveState();
$pdf->setStrokeColor(0,0,0,1);
//$pdf->line(20,40,578,40);
//$pdf->line(20,822,578,822);
$pdf->restoreState();
$pdf->closeObject();
$pdf->addObject($all,'all');
global $timedate;
$imgFileName = 'modules/Quotes/layouts/company.jpg';
//$pdf->addJpegFromFile($imgFileName,20,570,300);
$grid[0]['TITLE'] = $mod_strings['LBL_PDF_PROJECT_NAME'];
$grid[1]['TITLE'] = $mod_strings['LBL_DATE_START'];
$grid[2]['TITLE'] = $mod_strings['LBL_DATE_END'];
$grid[3]['TITLE'] = $mod_strings['LBL_LIST_FILTER_VIEW'];
$grid[4]['TITLE'] = $mod_strings['LBL_DATE'];

$grid[0]['VALUE'] = $locale->translateCharset($_REQUEST['project_name'], 'UTF-8', $locale->getExportCharset());
$grid[1]['VALUE'] = $locale->translateCharset($_REQUEST['project_start'], 'UTF-8', $locale->getExportCharset());
$grid[2]['VALUE'] = $locale->translateCharset($_REQUEST['project_end'], 'UTF-8', $locale->getExportCharset());
if ($_REQUEST['selected_view'] == '0' || $_REQUEST['selected_view'] == '1')
    $grid[3]['VALUE'] = $mod_strings['LBL_FILTER_ALL_TASKS'];
else if ($_REQUEST['selected_view'] == '2')
    $grid[3]['VALUE'] = $mod_strings['LBL_FILTER_COMPLETED_TASKS'];
if ($_REQUEST['selected_view'] == '3')
    $grid[3]['VALUE'] = $mod_strings['LBL_FILTER_INCOMPLETE_TASKS'];
if ($_REQUEST['selected_view'] == '4')
    $grid[3]['VALUE'] = $mod_strings['LBL_FILTER_MILESTONES'];
if ($_REQUEST['selected_view'] == '5')
    $grid[3]['VALUE'] = $mod_strings['LBL_FILTER_RESOURCE']. $locale->translateCharset(" ".$_REQUEST['view_filter_resource'], 'UTF-8', $locale->getExportCharset());
if ($_REQUEST['selected_view'] == '6')
    $grid[3]['VALUE'] = $mod_strings['LBL_FILTER_DATE_RANGE_START']. $locale->translateCharset(" ".$_REQUEST['view_filter_date_start']." ", 'UTF-8', $locale->getExportCharset()).
    $mod_strings['LBL_FILTER_DATE_RANGE_FINISH'].  $locale->translateCharset(" ".$_REQUEST['view_filter_date_finish'], 'UTF-8', $locale->getExportCharset());
if ($_REQUEST['selected_view'] == '7')
    $grid[3]['VALUE'] = $mod_strings['LBL_LIST_OVERDUE_TASKS']. $locale->translateCharset(" ".$_REQUEST['view_filter_resource'], 'UTF-8', $locale->getExportCharset());
if ($_REQUEST['selected_view'] == '8')
    $grid[3]['VALUE'] = $mod_strings['LBL_LIST_UPCOMING_TASKS']. $locale->translateCharset(" ".$_REQUEST['view_filter_resource'], 'UTF-8', $locale->getExportCharset());
if ($_REQUEST['selected_view'] == '9')
    $grid[3]['VALUE'] = $mod_strings['LBL_FILTER_MY_TASKS']. $locale->translateCharset(" ".$_REQUEST['view_filter_resource'], 'UTF-8', $locale->getExportCharset());
if ($_REQUEST['selected_view'] == '10')
    $grid[3]['VALUE'] = $mod_strings['LBL_FILTER_MY_OVERDUE_TASKS']. $locale->translateCharset(" ".$_REQUEST['view_filter_resource'], 'UTF-8', $locale->getExportCharset());
if ($_REQUEST['selected_view'] == '11')
    $grid[3]['VALUE'] = $mod_strings['LBL_FILTER_MY_UPCOMING_TASKS']. $locale->translateCharset(" ".$_REQUEST['view_filter_resource'], 'UTF-8', $locale->getExportCharset());

$grid[4]['VALUE'] = $locale->translateCharset(date("m/d/Y", time()), 'UTF-8', $locale->getExportCharset());


$options['showLines'] = 0;
$options['titleFontSize'] = 12;
$options['shadeHeadings'] = 0;
$options['headCol'] = array(.7,.3,.3);
$options['headTextCol'] = array(0,0,.4);
$options['xPos'] = 20;
$options['xOrientation'] = 'right';
$options['showHeadings'] = 0;
$options['shaded'] = 0;
$options['rowGap'] = 0;

$pdf->ezTable($grid,'' ,'' ,$options);
$pdf->addJpegFromFile($imgFileName,400,570,300);
//ezTable $options is an associative array which can contain:
//'showLines'=> 0,1,2, default is 1 (1->show the borders, 0->no borders, 2-> show borders AND lines between rows.)
//'showHeadings' => 0 or 1
//'shaded'=> 0,1,2, default is 1 (1->alternate lines are shaded, 0->no shading, 2->both sets are shaded)
//'shadeCol' => (r,g,b) array, defining the colour of the shading, default is (0.8,0.8,0.8)
//'shadeCol2' => (r,g,b) array, defining the colour of the shading of the second set, default is (0.7,0.7,0.7), used when 'shaded' is set to 2.
//'fontSize' => 10
//'textCol' => (r,g,b) array, text colour
//'titleFontSize' => 12
//'rowGap' => 2 , the space between the text and the row lines on each row 7 of 41 http://ros.co.nz/pdf - http://www.sourceforge.net/projects/pdf-php
//'colGap' => 5 , the space between the text and the column lines in each column
//'lineCol' => (r,g,b) array, defining the colour of the lines, default, black.
//'xPos' => 'left','right','center','centre',or coordinate, reference coordinate in the x-direction
//'xOrientation' => 'left','right','center','centre', position of the table w.r.t 'xPos'. This entry is to be used in conjunction with 'xPos' to give control over the lateral position of the table.
//'width' => <number>, the exact width of the table, the cell widths will be adjusted to give the table this width.
//'maxWidth' => <number>, the maximum width of the table, the cell widths will only be adjusted if the table width is going to be greater than this.

//$options['width'] = 300;



$pdf->ezSetDY(-15);
$lastY = $pdf->y ;
$options['shaded'] = 1;
$options['titleFontSize'] = 12;
$options['shadeHeadings'] = 1;
$options['headCol'] = array(.3,.3,.3);
$options['headTextCol'] = array(1,1,1);
$options['xPos'] = 15;
$options['xOrientation'] = 'right';
$options['showHeadings'] = 1;
$options['showLines'] = 0;

$pdf->ezSetY($lastY);
$options['xPos'] = 25 + $pdf->lastWidth;

if ($_REQUEST['numRowsToSave'] > 0) {
    for ($i = 1; $i <= $_REQUEST['numRowsToSave']; $i++) {
        //$val = $locale->translateCharset($val, 'UTF-8', $locale->getExportCharset());
        if (isset($_REQUEST["mapped_row_" . $i])) {
            $actualRow = $_REQUEST["mapped_row_" . $i];
            $item[$actualRow][$mod_strings['LBL_TASK_ID']] = $locale->translateCharset($_REQUEST["mapped_row_" . $i], 'UTF-8', $locale->getExportCharset());
            if ($_REQUEST['is_milestone_' . $i])  
                $item[$actualRow][$mod_strings['LBL_TASK_ID']] .= $locale->translateCharset('*', 'UTF-8', $locale->getExportCharset());
            $item[$actualRow][$mod_strings['LBL_PERCENT_COMPLETE']] = $locale->translateCharset($_REQUEST['percent_complete_' . $i], 'UTF-8', $locale->getExportCharset()); 
            $taskName =  str_replace("&nbsp;"," ",$_REQUEST['description_divlink_input_' . $i]);
            $item[$actualRow][$mod_strings['LBL_TASK_NAME']] = $locale->translateCharset($taskName, 'UTF-8', $locale->getExportCharset());
            $item[$actualRow][$mod_strings['LBL_DURATION']] = $locale->translateCharset($_REQUEST["duration_" . $i] . " " . $app_list_strings['project_duration_units_dom'][$_REQUEST["duration_unit_hidden_" . $i]], 
                'UTF-8', $locale->getExportCharset()); 
            $item[$actualRow][$mod_strings['LBL_START']] = $locale->translateCharset($_REQUEST['date_start_' . $i], 'UTF-8', $locale->getExportCharset()); 
            $item[$actualRow][$mod_strings['LBL_FINISH']] = $locale->translateCharset($_REQUEST['date_finish_' . $i], 'UTF-8', $locale->getExportCharset()); 
            $item[$actualRow][$mod_strings['LBL_PREDECESSORS']] = $locale->translateCharset($_REQUEST['predecessors_' . $i], 'UTF-8', $locale->getExportCharset()); 
            $item[$actualRow][$mod_strings['LBL_RESOURCE_NAMES']] = $locale->translateCharset($_REQUEST['resource_full_name_' . $i], 'UTF-8', $locale->getExportCharset());
            if (!empty($_REQUEST['actual_duration_' . $i]))            
                $item[$actualRow][$mod_strings['LBL_ACTUAL_DURATION']] = $locale->translateCharset($_REQUEST['actual_duration_' . $i]. " " . $app_list_strings['project_duration_units_dom'][$_REQUEST["duration_unit_hidden_" . $i]],
                    'UTF-8', $locale->getExportCharset());
            else
                $item[$actualRow][$mod_strings['LBL_ACTUAL_DURATION']] = $locale->translateCharset($_REQUEST['actual_duration_' . $i], 'UTF-8', $locale->getExportCharset());
        } 
    } 
    ksort($item);
}
$options['cols'] = array(
    $mod_strings['LBL_TASK_ID'] => array('justification' => 'left'),
    $mod_strings['LBL_PERCENT_COMPLETE'] => array('justification' => 'left'),
    $mod_strings['LBL_TASK_NAME'] => array('justification' => 'left'),
    $mod_strings['LBL_DURATION'] => array('justification' => 'right'),
    $mod_strings['LBL_START'] => array('justification' => 'left'),
    $mod_strings['LBL_FINISH'] => array('justification' => 'left'),
    $mod_strings['LBL_PREDECESSORS'] => array('justification' => 'left'),
    $mod_strings['LBL_RESOURCE_NAMES'] => array('justification' => 'left'),
    $mod_strings['LBL_ACTUAL_DURATION'] => array('justification' => 'left'),
);
$options['width'] = 750;
$options['xPos'] = 15;
$pdf->ezSetDy(-20);

$pdf->ezTable($item,'' ,'' ,$options);


$filename = preg_replace("#[^A-Z0-9\-_\.]#i", "_", 'Project');
$filename = "{$filename}.pdf";
$stream_options = array(
        'Content-Disposition' => $filename,
        'compress'            => 1,
);
$pdf->stream($stream_options);
?>