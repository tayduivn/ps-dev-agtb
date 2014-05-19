<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file=>You have unconditionally agreed to the
 * terms and conditions of the License=>and You may not use this file except in
 * compliance with the License.  Under the terms of the license=>You shall not,
 * among other things: 1) sublicense=>resell=>rent=>lease=>redistribute=>assign
 * or otherwise transfer Your rights to the Software=>and 2) use the Software
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
 * Your Warranty=>Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM=>Inc.; All Rights Reserved.
 ********************************************************************************/
/**
 * This array define the default value to use for the sugarpdf settings.
 * The order is DB (user or system) -> custom sugarpdf_default -> OOB sugarpdf_default
 */
$sugarpdf_default = array(
    "K_PATH_MAIN"=>"include/tcpdf/",
    "K_PATH_URL"=>"customized/include/tcpdf/",
    "K_PATH_FONTS"=>"include/tcpdf/fonts/",
    "K_PATH_CUSTOM_FONTS"=>"custom/include/tcpdf/fonts/",
    "K_PATH_CACHE"=> sugar_cached("include/tcpdf/"),
    "K_PATH_URL_CACHE"=> sugar_cached("include/tcpdf/"),
    "K_PATH_CUSTOM_IMAGES"=>"custom/themes/default/images/",
    "K_PATH_IMAGES"=>"themes/default/images/",
    "K_BLANK_IMAGE"=>"themes/default/images/_blank.png",
    "PDF_PAGE_FORMAT"=>"LETTER",
);
