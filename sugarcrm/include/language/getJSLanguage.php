<?php
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
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

/**
 * Retrieves the requested js language file, building it if it doesn't exist.
 */
function getJSLanguage()
{

    require_once ('include/language/jsLanguage.php');

    global $app_list_strings;

    if (empty($_REQUEST['lang'])) {
        echo "No language specified";

        return;
    }

    $lang = clean_path($_REQUEST['lang']);
    $languages = get_languages();

    if (!preg_match("/^\w\w_\w\w$/", $lang) || !isset($languages[$lang])) {
        if (!preg_match("/^\w\w_\w\w$/", $lang)) {
            echo "did not match regex<br/>";
        } else {
            echo  "$lang was not in list . <pre>" . print_r($languages, true) . "</pre>";
        }
        echo "Invalid language specified";

        return;
    }
    if (empty($_REQUEST['module']) || $_REQUEST['module'] === 'app_strings') {
        $file = sugar_cached('jsLanguage/') . $lang . '.js';
        if (!sugar_is_file($file)) {
            jsLanguage::createAppStringsCache($lang);
        }
    } else {
        $module = clean_path($_REQUEST['module']);
        $fullModuleList = array_merge($GLOBALS['moduleList'], $GLOBALS['modInvisList']);
        if (!isset($app_list_strings['moduleList'][$module]) && !in_array($module, $fullModuleList)) {
            echo "Invalid module specified";

            return;
        }
        $file = sugar_cached('jsLanguage/') . $module . "/" . $lang . '.js';
        if (!sugar_is_file($file)) {
            jsLanguage::createModuleStringsCache($module, $lang);
        }
    }

    //Setup cache headers
    header("Content-Type: application/javascript");
    header("Cache-Control: max-age=31556940, private");
    header("Pragma: ");
    header("Expires: " . gmdate('D, d M Y H:i:s \G\M\T', time() + 31556940));

    readfile($file);
}

getJSLanguage();
