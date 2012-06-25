<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
//FILE SUGARCRM flav=pro ONLY

require 'config.php';
require_once 'include/Sugarpdf/sugarpdf_config.php';
global $sugar_config;
global $timedate;
global $mod_strings;

$pdfTemplate = new PdfManager();
$pdfTemplate->base_module = 'Quotes';
$pdfTemplate->name = $mod_strings['pdf_template_quote']['name'];
$pdfTemplate->description = $mod_strings['pdf_template_quote']['description'];
$pdfTemplate->body_html = to_html($mod_strings['pdf_template_quote']['body_html']);
$pdfTemplate->author = PDF_AUTHOR;
$pdfTemplate->title = PDF_HEADER_TITLE;
$pdfTemplate->subject = PDF_SUBJECT;
$pdfTemplate->keywords = PDF_KEYWORDS;
$pdfTemplate->published = 'yes';
$pdfTemplate->deleted = 0;
$pdfTemplate->team_id = 1;
$pdfTemplate->save();

$pdfTemplate = new PdfManager();
$pdfTemplate->base_module = 'Quotes';
$pdfTemplate->name = $mod_strings['pdf_template_invoice']['name'];
$pdfTemplate->description = $mod_strings['pdf_template_invoice']['description'];
$pdfTemplate->body_html = to_html($mod_strings['pdf_template_invoice']['body_html']);
$pdfTemplate->author = PDF_AUTHOR;
$pdfTemplate->title = PDF_HEADER_TITLE;
$pdfTemplate->subject = PDF_SUBJECT;
$pdfTemplate->keywords = PDF_KEYWORDS;
$pdfTemplate->published = 'yes';
$pdfTemplate->deleted = 0;
$pdfTemplate->team_id = 1;
$pdfTemplate->save();


$pdfTemplateImageId = create_guid();
$file = 'themes/default/images/' . PDF_SMALL_HEADER_LOGO;
$newfile = "upload://$pdfTemplateImageId";
@copy($file, $newfile);

$pdfTemplate = new PdfManager();
$pdfTemplate->base_module = 'Reports';
$pdfTemplate->name = $mod_strings['pdf_template_reports']['name'];
$pdfTemplate->description = $mod_strings['pdf_template_reports']['description'];
$pdfTemplate->header_image = $pdfTemplateImageId;
$pdfTemplate->header_image_ext = substr(strrchr(PDF_SMALL_HEADER_LOGO, '.'), 1);
$pdfTemplate->author = PDF_AUTHOR;
$pdfTemplate->title = PDF_HEADER_TITLE;
$pdfTemplate->subject = PDF_SUBJECT;
$pdfTemplate->keywords = PDF_KEYWORDS;
$pdfTemplate->published = 'yes';
$pdfTemplate->deleted = 0;
$pdfTemplate->team_id = 1;
$pdfTemplate->save();
