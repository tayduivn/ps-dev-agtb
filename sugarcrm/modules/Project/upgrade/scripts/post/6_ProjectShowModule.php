<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

/**
 * If someone is using the Projects module, re-enable it
 */
class SugarUpgradeProjectShowModule extends UpgradeScript
{
    public $order = 6999;
    public $type = self::UPGRADE_CORE;

    public function run()
    {

        $path = 'custom/Extension/application/Ext';
        $file_name = 'project_unhide.php';
        $projectModuleEnabled = ($this->db->tableExists("project") && $this->db->fetchOne("SELECT id FROM project"));
        if ($projectModuleEnabled && !SugarAutoLoader::fileExists($path . '/Include/' . $file_name)) {

            // add in subpanel for Opportunities
            $sub_panel_path = 'custom/Extension/modules/Opportunities/Ext/clients/base/layouts/subpanels/';
            if (!sugar_is_dir($sub_panel_path)) {
                sugar_mkdir($sub_panel_path, null, true);
            }

            $file_contents = '
<?php
// WARNING: The contents of this file are auto-generated.

$viewdefs["Opportunities"]["base"]["layout"]["subpanels"]["components"][] = array (
            "layout" => "subpanel",
            "label" => "LBL_PROJECTS_SUBPANEL_TITLE",
            "context" => array (
                "link" => "project",
            ),
);
';
            sugar_file_put_contents($sub_panel_path . $file_name, $file_contents);

            if (!sugar_is_dir($path . '/Include/')) {
                sugar_mkdir($path . '/Include/', null, true);
            }

            $file_contents = '
<?php
// WARNING: The contents of this file are auto-generated.

$moduleList[] = \'Project\';

if (isset($modInvisList) && is_array($modInvisList)) {
    foreach($modInvisList as $key => $mod) {
        if($mod == \'Project\' || $mod == \'ProjectTask\') {
            unset($modInvisList[$key]);
        }
    }
}
';

            // enable the project module in the upgrade instance
            global $moduleList, $modInvisList;
            $moduleList[] = 'Project';
            foreach ($modInvisList as $key => $mod) {
                if ($mod == 'Project' || $mod == 'ProjectTask') {
                    unset($modInvisList[$key]);
                }
            }

            sugar_file_put_contents($path . '/Include/' . $file_name, $file_contents);

            // write out the language file
            $lang_file_contents = '
<?php
// WARNING: The contents of this file are auto-generated.

$app_list_strings[\'moduleList\'][\'Project\'] = \'Projects\';
$app_list_strings[\'moduleList\'][\'ProjectTask\'] = \'Project Tasks\';

$app_list_strings[\'moduleListSingular\'][\'Project\'] = \'Project\';
$app_list_strings[\'moduleListSingular\'][\'ProjectTask\'] = \'Project Task\';

$app_list_strings[\'record_type_display\'][\'Project\'] = \'Project\';
$app_list_strings[\'record_type_display\'][\'ProjectTask\'] = \'Project Task\';

$app_list_strings[\'record_type_display_notes\'][\'Project\'] = \'Project\';
$app_list_strings[\'record_type_display_notes\'][\'ProjectTask\'] = \'Project Task\';

$app_list_strings[\'parent_type_display\'][\'Project\'] = \'Project\';
$app_list_strings[\'parent_type_display\'][\'ProjectTask\'] = \'Project Task\';

$app_list_strings[\'product_category_dom\'][\'Projects\'] = \'Projects\';
            
$app_list_strings[\'projects_priority_options\'] = array(
    \'high\' => \'High\',
    \'medium\' => \'Medium\',
    \'low\' => \'Low\',
);

$app_list_strings[\'projects_status_options\'] = array(
    \'notstarted\' => \'Not Started\',
    \'inprogress\' => \'In Progress\',
    \'completed\' => \'Completed\',
);

$app_list_strings[\'project_priority_default\'] = \'Medium\';
$app_list_strings[\'project_priority_options\'] = array(
    \'High\' => \'High\',
    \'Medium\' => \'Medium\',
    \'Low\' => \'Low\',
);

$app_list_strings[\'project_task_priority_options\'] = array(
    \'High\' => \'High\',
    \'Medium\' => \'Medium\',
    \'Low\' => \'Low\',
);
$app_list_strings[\'project_task_priority_default\'] = \'Medium\';

$app_list_strings[\'project_task_status_options\'] = array(
    \'Not Started\' => \'Not Started\',
    \'In Progress\' => \'In Progress\',
    \'Completed\' => \'Completed\',
    \'Pending Input\' => \'Pending Input\',
    \'Deferred\' => \'Deferred\',
);
$app_list_strings[\'project_task_utilization_options\'] = array(
    \'0\' => \'none\',
    \'25\' => \'25\',
    \'50\' => \'50\',
    \'75\' => \'75\',
    \'100\' => \'100\',
);

$app_list_strings[\'project_status_dom\'] = array(
    \'Draft\' => \'Draft\',
    \'In Review\' => \'In Review\',
    \'Published\' => \'Published\',
);
$app_list_strings[\'project_status_default\'] = \'Draft\';

$app_list_strings[\'project_duration_units_dom\'] = array(
    \'Days\' => \'Days\',
    \'Hours\' => \'Hours\',
);

$app_list_strings[\'project_priority_options\'] = array(
    \'High\' => \'High\',
    \'Medium\' => \'Medium\',
    \'Low\' => \'Low\',
);

$app_strings[\'LBL_PROJECT_MINUS\'] = \'Remove\';
$app_strings[\'LBL_PROJECT_PLUS\'] = \'Add\';
';

            if (!sugar_is_dir($path. '/Language/')) {
                sugar_mkdir($path . '/Language/', null, true);
            }

            sugar_file_put_contents($path . '/Language/en_us-' . $file_name, $lang_file_contents);
        } elseif ($projectModuleEnabled == false
            && SugarAutoLoader::existing('custom/modules/unified_search_modules_display.php')) {
            // we need to clean out the unified search cache
            $unified_search_modules_display = array();
            include('custom/modules/unified_search_modules_display.php');


            unset($unified_search_modules_display['Project']);
            unset($unified_search_modules_display['ProjectTask']);

            write_array_to_file(
                "unified_search_modules_display",
                $unified_search_modules_display,
                'custom/modules/unified_search_modules_display.php'
            );
            SugarCache::cleanFile('custom/modules/unified_search_modules_display.php');
        }
    }
}
