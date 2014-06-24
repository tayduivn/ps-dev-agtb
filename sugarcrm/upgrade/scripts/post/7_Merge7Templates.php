<?php
 if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
 * Merge Sugar 7 templates with customizations
 * Uses data from pre-script Merge7
 * @see BR-1491
 */
class SugarUpgradeMerge7Templates extends UpgradeScript
{
    public $order = 7000;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        if(version_compare($this->from_version, '7.0', '<')) {
            // Not needed from upgrades from Sugar 6
            return;
        }

        if(empty($this->upgrader->state['for_merge'])) {
            // no views to upgrade
            return;
        }

        foreach($this->upgrader->state['for_merge'] as $filename => $old_viewdefs) {
            $this->mergeView($filename, $old_viewdefs);
        }
    }

    /**
     * Load view file
     * @param string $filename
     * @param string $module_name
     * @param string $platform
     * @param string $viewname
     * @return NULL|array
     */
    protected function loadFile($filename, $module_name, $platform, $viewname)
    {
        $viewdefs = array();
        include $filename;
        if(empty($viewdefs) || empty($viewdefs[$module_name][$platform]['view'][$viewname]['panels'])) {
            // we do not handle non-panel views for now
            return null;
        }
        return $viewdefs;
    }

    /**
     * Extract field list from panels
     * @param array $panels
     * @return array
     */
    protected function fieldList($panels)
    {
        $fields = array();
        foreach($panels as $pindex => $panel) {
            if(empty($panel['fields'])) {
                continue;
            }
            if(!empty($panel['name'])) {
                $pname = $panel['name'];
            } else {
                $pname = null;
            }
            foreach($panel['fields'] as $fieldno => $field) {
                if(is_array($field)) {
                    if(empty($field['name'])) {
                        // omit no-name fields
                        continue;
                    }
                    $fname = $field['name'];
                } else {
                    $fname = $field;
                }
                $fields[$fname] = array("pname" => $pname, "pindex" => $pindex, "findex" => $fieldno, "data" => $field);
            }
        }
        return $fields;
    }

    /**
     * Merge view data
     * @param string $filename
     * @param array $old_viewdefs
     */
    protected function mergeView($filename, $old_viewdefs)
    {
        $needSave = false;
        $this->log("Merging view $filename");
        list($modules, $module_name, $clients, $platform, $views, $viewname) = explode(DIRECTORY_SEPARATOR, $filename);

        $new_viewdefs = $this->loadFile($filename, $module_name, $platform, $viewname);
        $custom_viewdefs =  $this->loadFile("custom/$filename", $module_name, $platform, $viewname);

        // These checks duplicate ones in Merge7, but better safe than sorry
        if(empty($old_viewdefs) || empty($new_viewdefs) || empty($custom_viewdefs)) {
            // defs missing - can't do anything here
            return;
        }
        if($old_viewdefs[$module_name][$platform]['view'][$viewname]['panels'] == $new_viewdefs[$module_name][$platform]['view'][$viewname]['panels']
        || $custom_viewdefs[$module_name][$platform]['view'][$viewname]['panels'] == $new_viewdefs[$module_name][$platform]['view'][$viewname]['panels']) {
            // no changes to handle
            return;
        }

        $old_fields = $this->fieldList($old_viewdefs[$module_name][$platform]['view'][$viewname]['panels']);
        $new_fields = $this->fieldList($new_viewdefs[$module_name][$platform]['view'][$viewname]['panels']);

        // Here we care only for field presence, not for changes in field metadata
        $removed_fields = array_udiff_assoc($old_fields, $new_fields, function () { return 0; });
        $added_fields = array_udiff_assoc($new_fields, $old_fields, function () { return 0; });
        // This may include also added & removed fields, we'll remove them later
        $changed_fields = array_udiff_assoc($new_fields, $old_fields, function($a, $b) { return $a == $b?0:-1; });

        if(empty($added_fields) && empty($removed_fields) && empty($changed_fields)) {
            // nothing to do
            return;
        }
        $this->log("Fields added: ".var_export($added_fields, true));
        $this->log("Fields removed: ".var_export($removed_fields, true));
        // Index custom fields too
        $custom_fields = $this->fieldList($custom_viewdefs[$module_name][$platform]['view'][$viewname]['panels']);

        foreach($added_fields as $field => $data) {
            unset($changed_fields[$field]);
            if(!empty($custom_fields[$field])) {
                // Already in custom view - we're done
                continue;
            }
            $this->addField($custom_viewdefs[$module_name][$platform]['view'][$viewname]['panels'], $data['pindex'], $data['pname'], $data['data']);
            $needSave = true;
        }

        foreach($removed_fields as $field => $data) {
            unset($changed_fields[$field]);
            if(empty($custom_fields[$field])) {
                // If this field not in custom view, we're done
                continue;
            }

            $pindex = $custom_fields[$field]['pindex'];
            $findex = $custom_fields[$field]['findex'];
            // Remove field from panel
            unset($custom_viewdefs[$module_name][$platform]['view'][$viewname]['panels'][$pindex]['fields'][$findex]);
            // Re-index the fields
            $custom_viewdefs[$module_name][$platform]['view'][$viewname]['panels'][$pindex]['fields'] = array_values($custom_viewdefs[$module_name][$platform]['view'][$viewname]['panels'][$pindex]['fields']);
            $needSave = true;
        }

        if(!empty($changed_fields)) {
            $this->log("Fields changed: ".var_export($added_fields, true));
            foreach ($changed_fields as $field => $data) {
                if(empty($custom_fields[$field]) || empty($old_fields[$field]) || empty($new_fields[$field])) {
                    // Custom has no such field - ignore it
                    // Other ones should not be empty since we'd catch them on added/removed but check anyway for safety
                    continue;
                }
                // Change only if custom matches old data
                if($custom_fields[$field]['data'] == $old_fields[$field]['data']) {
                    $pindex = $custom_fields[$field]['pindex'];
                    $findex = $custom_fields[$field]['findex'];
                    $custom_viewdefs[$module_name][$platform]['view'][$viewname]['panels'][$pindex]['fields'][$findex] = $new_fields[$field]['data'];
                    $needSave = true;
                }
            }
        }

        if($needSave) {
            $this->log("Saving updated custom/$filename");
            write_array_to_file("viewdefs['$module_name']['$platform']['view']['$viewname']", $custom_viewdefs[$module_name][$platform]['view'][$viewname], "custom/$filename");
        }
    }

    /**
     * Add a field to panel, trying to match panel name
     * @param array &$panels Panels list
     * @param array $pindex Index of the panel to update
     * @param array $pname Name of the panel to update
     * @param array $field Field data
     */
    protected function addField(&$panels, $pindex, $pname, $field)
    {
        // Try by name
        foreach($panels as $cpindex => $panel) {
            if(!empty($panel['name']) && $panel['name'] == $pname) {
                $panels[$cpindex]['fields'][] = $field;
                return;
            }
        }
        if(empty($panels[$pindex])) {
            // if we do not have this index, use last panel
            end($panels);
            $pindex = key($panels);
        }
        // add to panel by index
        $panels[$pindex]['fields'][] = $field;
    }

}
