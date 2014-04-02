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
 * Fix detail views that may be broken by MergeTemplate
 * See BR-1462
 */
class SugarUpgradeFixDetailView extends UpgradeScript
{
    public $order = 5000;
    public $type = self::UPGRADE_CUSTOM;
    // List of modules from EditViewMerge.php which may be messed up
    // These are BWC modules that DetailView merger could mess up
    protected $modules = array('Campaigns', 'Meetings', 'Contracts');
    // Broken fields. Label is produced by MergeTemplates, customCode is the original code that should be there
    protected $fields_names = array(
        'date_modified' => array(
                'label'=> 'LBL_MODIFIED_NAME',
                'customCode' => '{$fields.date_modified.value} {$APP.LBL_BY} {$fields.modified_by_name.value}'),
        'date_entered' => array(
                'label' => 'LBL_CREATED',
                'customCode' => '{$fields.date_entered.value} {$APP.LBL_BY} {$fields.created_by_name.value}'),
    );

    public function run()
    {
        if(version_compare($this->from_version, '7.0', '>=')) {
            // right now there's no need to run this on 7
            return;
        }
        foreach($this->modules as $module) {
            $this->fixModule($module);
        }
    }

    protected function fixModule($module) {
        if(!isModuleBWC($module)) {
            $this->log("$module is not BWC, not checking");
            continue;
        }
        $filename = "custom/modules/$module/metadata/detailviewdefs.php";
        if(file_exists($filename)) {
            $this->log("Checking $filename");
            $viewdefs = array();
            include $filename;
            if(empty($viewdefs[$module]) || empty($viewdefs[$module]['DetailView']['panels'])) {
                $this->log("Could not find viewdefs, skipping");
                continue;
            }
            $modified = false;
            foreach($viewdefs[$module]['DetailView']['panels'] as $pname => $panel) {
                foreach($panel as $rid => $row) {
                    foreach($row as $fid => $field) {
                        // Check that the field is one of the broken fields and has broken label
                        // and no custom code
                        if(is_array($field) && !empty($field['name']) && !empty($field['label'])
                            && !isset($field['customCode'])
                            && !empty($this->fields_names[$field['name']])
                            && $this->fields_names[$field['name']]['label'] == $field['label']) {
                            // Reset field to using proper custom code
                            $newfield = array('name' => $field['name'], 'customCode' => $this->fields_names[$field['name']]['customCode']);
                            $viewdefs[$module]['DetailView']['panels'][$pname][$rid][$fid] = $newfield;
                            $modified = true;
                        }
                    }
                }
            }
            if($modified) {
                $this->log("Updating $filename");
                write_array_to_file("viewdefs['$module']['DetailView']", $viewdefs[$module]['DetailView'], $filename);
            }
        }
    }

}
