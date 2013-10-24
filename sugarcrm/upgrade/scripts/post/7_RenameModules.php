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
/**
 * Update data for renamed modules
 */
class SugarUpgradeRenameModules extends UpgradeScript
{
    public $order = 7000;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        require_once('modules/Studio/wizards/RenameModules.php');
        require_once('include/utils.php');

        $klass = new RenameModules();
        $languages = get_languages();

        foreach ($languages as $langKey => $langName) {
            //get list strings for this language
            $strings = return_app_list_strings_language($langKey);

            //get base list strings for this language
            if (file_exists("include/language/$langKey.lang.php")) {
                include("include/language/$langKey.lang.php");

                //Keep only renamed modules
                $renamedModules = array_diff($strings['moduleList'], $app_list_strings['moduleList']);

                foreach ($renamedModules as $moduleId => $moduleName) {
                    if(isset($app_list_strings['moduleListSingular'][$moduleId])) {
                        $klass->selectedLanguage = $langKey;

                        $replacementLabels = array(
                            'singular' => $strings['moduleListSingular'][$moduleId],
                            'plural' => $strings['moduleList'][$moduleId],
                            'prev_singular' => $app_list_strings['moduleListSingular'][$moduleId],
                            'prev_plural' => $app_list_strings['moduleList'][$moduleId],
                            'key_plural' => $moduleId,
                            'key_singular' => $klass->getModuleSingularKey($moduleId)
                        );
                        $klass->changeModuleModStrings($moduleId, $replacementLabels);
                    }
                }
            }
        }
    }
}
