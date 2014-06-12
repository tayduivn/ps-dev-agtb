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
// This will need to be pathed properly when packaged
require_once 'SidecarAbstractMetaDataUpgrader.php';
require_once 'modules/ModuleBuilder/Module/StudioModuleFactory.php';

class SidecarFilterMetaDataUpgrader extends SidecarAbstractMetaDataUpgrader
{
    /**
     * Should we delete pre-upgrade files?
     * Not deleting searchviews since we may need them for popups in subpanels driven by BWC module.
     * See BR-1044
     * @var bool
     */
    public $deleteOld = false;

    /**
     * Load search fields defs from SearchFields.php
     * @return array
     */
    protected function loadSearchFields()
    {
        $filename = dirname($this->fullpath)."/SearchFields.php";

        if(!file_exists($filename)) {
            // try without custom
            if(substr($filename, 0, 7) == 'custom/') {
                $filename = substr($filename, 7);
            }

            if(!file_exists($filename)) {
                 // try going to module directly
                $filename = "modules/{$this->module}/metadata/SearchFields.php";

                if(!file_exists($filename)) {
                    // try template now
                    $sm = StudioModuleFactory::getStudioModule($this->module);
                    $moduleType = $sm->getType();
                    $filename = 'include/SugarObjects/templates/' . $moduleType . '/metadata/SearchFields.php';

                    if(!file_exists($filename)) {
                        // OK, I give up, no way I can find it, let's use basic ones
                        $filename = 'include/SugarObjects/templates/basic/metadata/SearchFields.php';
                    }
                }
            }
        }
        $searchFields = array();
        $module_name = $this->module;
        include $filename;
        return $searchFields[$module_name];
    }

    /**
     * Check if we actually want to upgrade this file
     * @return boolean
     */
    public function upgradeCheck()
    {
        $target = $this->getNewFileName($this->viewtype);
        if(file_exists($target)) {
            // if we already have the target, skip the upgrade
            return false;
        }
        return true;
    }

    /**
     * Does nothing for search since search is simply a file move.
     */
    public function convertLegacyViewDefsToSidecar()
    {
        // load SearchFields.php
        $searchFields = $this->loadSearchFields();

        $fields = array();
        if(!empty($this->legacyViewdefs['layout']['basic_search'])) {
            $old_fields = $this->legacyViewdefs['layout']['basic_search'];
        } else {
            $old_fields = array();
        }
        if(!empty($this->legacyViewdefs['layout']['advanced_search'])) {
            $old_fields = array_merge($old_fields, $this->legacyViewdefs['layout']['advanced_search']);
        }
        foreach($old_fields as $name => $data) {
            if(!empty($data['name'])) {
                $name = $data['name'];
            }
            // We'll add those later
            if($name == 'favorites_only' || $name == 'current_user_only') continue;

            // Also try range_* for date fields, see BR-1409
            if(!empty($searchFields["range_".$name])) {
                $searchFields[$name] = $searchFields["range_".$name];
            }

            // We don't know this field
            if(empty($searchFields[$name])) {
                // may be a custom field
                if(substr($name, -2) == '_c') {
                    $fields[$name] = $data;
                    if(isset($fields[$name]['label']) && !isset($fields[$name]['vname'])) {
                        $fields[$name]['vname'] = $fields[$name]['label'];
                        unset($fields[$name]['label']);
                    }
                    continue;
                }
            }

            // Subqueries not supported yet
            if(!empty($searchFields[$name]['operator']) && $searchFields[$name]['operator'] == 'subquery') continue;

            if(!empty($searchFields[$name]['db_field'])) {
                $label = '';
                if(isset($data['label'])) {
                    $label = $data['label'];
                }
                if(isset($searchFields[$name]['vname'])) {
                    $label = $searchFields[$name]['vname'];
                }
                $fields[$name] = array(
                    'dbFields' => array_filter($searchFields[$name]['db_field'], array($this, "isValidField")),
                );
                if (!empty($searchFields[$name]['type'])) {
                    $fields[$name]['type'] = $searchFields[$name]['type'];
                }
                if (empty($fields[$name]['dbFields']) && !$this->isValidField($name)) {
                    unset($fields[$name]);
                    continue;
                }
                if(!empty($label)) {
                    $fields[$name]['vname'] = $label;
                }
            } else {
                if (!$this->isValidField($name)) {
                    continue;
                }
                $fields[$name] = array();
            }
        }
        $fields['$owner'] = array(
            'predefined_filter' => true,
            'vname' => 'LBL_CURRENT_USER_FILTER',
        );
        $fields['$favorite'] = array(
                'predefined_filter' => true,
                'vname' => 'LBL_FAVORITES_FILTER',
        );
        $this->sidecarViewdefs = array(
            'default_filter' => 'all_records',
        );
        $this->sidecarViewdefs['fields'] = $fields;
    }

    public function getNewFileName($viewname)
    {
        $client = $this->client == 'wireless' ? 'mobile' : $this->client;
        // Cut off metadata/searchdefs.php
        $dirname = dirname(dirname($this->fullpath));
        return $dirname . "/clients/$client/filters/default/default.php";
    }

    public function getNewFileContents($viewname)
    {
        $module = $this->getNormalizedModuleName();
        $viewname = MetaDataFiles::getName($viewname);
        $client = $this->client == 'wireless' ? 'mobile' : $this->client;
        $out  = "<?php\n\$viewdefs['{$module}']['{$client}']['filter']['default'] = " . var_export($this->sidecarViewdefs, true) . ";\n";
        return $out;
    }

}