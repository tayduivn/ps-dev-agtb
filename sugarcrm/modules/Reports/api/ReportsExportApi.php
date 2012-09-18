<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
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

require_once('data/BeanFactory.php');
require_once('include/download_file.php');
require_once('modules/Reports/ReportCache.php');
/**
 * @api
 */
class ReportsExportApi extends SugarApi {
    // how long the cache is ok, in minutes
    private $cacheLength = 10;

    public function registerApiRest() {
        return array(
            'exportRecord' => array(
                'reqType' => 'GET',
                'path' => array('Reports', '?', '?'),
                'pathVars' => array('module', 'record', 'export_type'),
                'method' => 'exportRecord',
                'shortHelp' => 'This method exports a record in the specified type',
                'longHelp' => '',
            ),
        );
    }

    /**
     * Export Report records into various files, now just pdf
     * @param ServiceBase $api The service base
     * @param array $args Arguments array built by the service base
     * @return binary file
     */
    public function exportRecord($api, $args)
    {

        $this->requireArgs($args,array('record', 'export_type'));
        $args['module'] = 'Reports';
        $GLOBALS['disable_date_format'] = FALSE;


        $method = 'export' . ucwords($args['export_type']);

        if(!method_exists($this, $method)) {
            throw new SugarApiExceptionNoMethod('Export Type Does Not Exists');
        }

        $saved_report = $this->loadBean($api, $args, 'view');
        
        return $this->$method($saved_report);

    }

    /**
     * Export a Base64 PDF Report
     * @param SugarBean report
     * @return file contents
     */
    protected function exportBase64(SugarBean $report)
    {
        global  $beanList, $beanFiles;
        global $sugar_config,$current_language;
        require_once('modules/Reports/templates/templates_pdf.php');
        $contents = '';
        $report_filename = false;
        if($report->id != null)
        {
            //Translate pdf to correct language
            $reporter = new Report(html_entity_decode($report->content), '', '');
            $reporter->layout_manager->setAttribute("no_sort",1);
            //Translate pdf to correct language
            $mod_strings = return_module_language($current_language, 'Reports');

            //Generate actual pdf
            $report_filename = template_handle_pdf($reporter, false);

            sugar_cache_put($report->id . '-' . $GLOBALS['current_user']->id, $report_filename, $this->cacheLength * 60);
         
            $dl = new DownloadFile();
            $contents = $dl->getFileByFilename($report_filename);
            if(empty($contents)) {
                throw new SugarApiException('File contents empty.');
            }            
        }
        return array('file_contents' => base64_encode($contents));
    }

    /**
     * Export a Report As PDF
     * @param SugarBean $report 
     * @return null
     */
    protected function exportPdf(SugarBean $report)
    {
        global  $beanList, $beanFiles;
        global $sugar_config,$current_language;
        require_once('modules/Reports/templates/templates_pdf.php');
        $report_filename = false;
        if($report->id != null)
        {
            //Translate pdf to correct language
            $reporter = new Report(html_entity_decode($report->content), '', '');
            $reporter->layout_manager->setAttribute("no_sort",1);
            //Translate pdf to correct language
            $mod_strings = return_module_language($current_language, 'Reports');

            //Generate actual pdf
            $report_filename = template_handle_pdf($reporter, false);



            header("Pragma: public");
            header("Cache-Control: maxage=1, post-check=0, pre-check=0");
            header("Content-Type: application/x-pdf");
            header("Content-Type: application/force-download");
            header("Content-type: application/octet-stream");
            header("Content-Disposition: attachment; filename=\"".basename($report_filename)."\";");
            header("X-Content-Type-Options: nosniff");
            header("Content-Length: " . filesize($report_filename));
            header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 2592000));
            set_time_limit(0);
            ob_start();


            //BEGIN SUGARCRM flav=int ONLY
            // awu: stripping out zend_send_file function call, the function changes the filename to be whatever is on the file system
            if(function_exists('zend_send_file')){
                zend_send_file($report_filename);
            }else{
            //END SUGARCRM flav=int ONLY
                readfile($report_filename);
            //BEGIN SUGARCRM flav=int ONLY
            }
            //END SUGARCRM flav=int ONLY
            @ob_end_flush();
        }

    }
}