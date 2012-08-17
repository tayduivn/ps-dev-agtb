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

class ReportsExportApi extends SugarApi {
    public function registerApiRest() {
        return array(
            'pdf' => array(
                'reqType' => 'GET',
                'path' => array('Reports', '?', 'pdf'),
                'pathVars' => array('Reports', 'record', 'export_type'),
                'method' => 'exportRecord',
                'shortHelp' => 'This method exports a record in the specified type',
                'longHelp' => 'include/api/html/export.html',
            ),
        );
    }

    public function export($api, $args)
    {
        global  $beanList, $beanFiles;
        global $sugar_config,$current_language;
        $this->requireArgs($args,array('record', 'export_type'));
        $args['module'] = 'SavedReport';
 
        $GLOBALS['disable_date_format'] = FALSE;
        require_once('modules/Reports/templates/templates_pdf.php');

        $saved_report = $this->loadBean($api, $args, 'view');

        $contents = '';
        if($saved_report->id != null)
        {
            $reporter = new Report(html_entity_decode($saved_report->content));
            $reporter->layout_manager->setAttribute("no_sort",1);
            //Translate pdf to correct language
            $module_for_lang = $reporter->module;
            $mod_strings = return_module_language($current_language, 'Reports');

            //Generate actual pdf
            $report_filename = template_handle_pdf($reporter, false);

            //Get file pdf file contents
            $contents = self::$helperObject->get_file_contents_base64($report_filename, TRUE);
        }

        return array('file_contents' => $contents);

        $GLOBALS['log']->info('End: SugarWebServiceImpl->get_report_pdf');        

    }
}