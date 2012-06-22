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

require_once('include/Sugarpdf/SugarpdfFactory.php');
require_once('modules/Reports/Report.php');


function preprocess($type = NULL, $reporter){
    $pdf = SugarpdfFactory::loadSugarpdf($type, "Reports", $reporter, array());
    return $pdf;
}

function process($pdf, $reportname, $stream){
    global $current_user;

    $pdf->process();
    @ob_clean();
    $filenamestamp = '';
    if(isset($current_user)){
        $filenamestamp .= '_'.$current_user->user_name;
    }
    $filenamestamp .= '_'.date(translate('LBL_PDF_TIMESTAMP', 'Reports'), time());
    $cr = array(' ',"\r", "\n","/");
    $filename = str_replace($cr, '_', $reportname.$filenamestamp.'.pdf');
    if(isset($_SERVER['HTTP_USER_AGENT']) && preg_match("/MSIE/", $_SERVER['HTTP_USER_AGENT'])) {
        $filename = urlencode($filename);
    }
    if($stream){
        //Force download as a file
        $pdf->Output($filename,'D');
    }else{
        $cachefile = sugar_cached('pdf/').$filename;
        $fp = sugar_fopen($cachefile, 'w');
        fwrite($fp, $pdf->Output('','S'));
        fclose($fp);

        return $cachefile;
    }
    return $filename;
}


/**
 * @return stream or string
 */
function template_handle_pdf(&$reporter, $stream = true, $pdf_template_id = '') {
    global $app_list_strings, $locale, $timedate;
    
    $reporter->enable_paging = false;
    $reporter->plain_text_output = true;

    if($reporter->report_type == 'summary' && !empty($reporter->report_def['summary_columns'])) {
        if($reporter->show_columns
            && !empty($reporter->report_def['display_columns'])
            && !empty($reporter->report_def['group_defs'])) {
            $type = "summary_combo";
        } elseif($reporter->show_columns
            && !empty($reporter->report_def['display_columns'])
            && empty($reporter->report_def['group_defs'])) {
            $type = "detail_and_total";
        } elseif(!empty($reporter->report_def['group_defs'])) {
            $type = "summary";
        } else {
            $type = "total";
        }
    } elseif(!empty($reporter->report_def['display_columns'])) {
        $type = "listview";
    }

    $pdf=preprocess($type, $reporter);
    
    //manage metadata & header image for the selected template
    $pdfTemplate = BeanFactory::newBean('PdfManager');
    if (!empty($pdf_template_id) && $pdfTemplate->retrieve($pdf_template_id) !== null) {
        $pdf->tplHeaderData = array();
        if (!empty($pdfTemplate->header_image)) {
            $tplImage = $GLOBALS['sugar_config']['upload_dir'].$pdfTemplate->header_image;
            $tplTargetImage = K_PATH_CUSTOM_IMAGES.$pdfTemplate->header_image.".".$pdfTemplate->header_image_ext;

            sugar_mkdir(K_PATH_CUSTOM_IMAGES, 0755, true);
            $tpl_filename = $pdfTemplate->header_image.".".$pdfTemplate->header_image_ext;
            if (    !file_exists($tplTargetImage) || 
                    (file_exists($tplTargetImage) && filesize($tplTargetImage) != filesize($tplImage)) 
                ) {
                if (!copy($tplImage, $tplTargetImage)){
                    $tpl_filename = "";
                }
            }
            if (!empty($tpl_filename)) {
                $pdf->tplHeaderData['logo'] = $tpl_filename;
            }
        }
        $pdf->tplHeaderData['author'] = $pdfTemplate->author;
        $pdf->tplHeaderData['title'] = $pdfTemplate->title;
        $pdf->tplHeaderData['subject'] = $pdfTemplate->subject;
        $pdf->tplHeaderData['keywords'] = $pdfTemplate->keywords;
    }
    return process($pdf, $reporter->name, $stream);
}