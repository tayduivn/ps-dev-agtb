<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

require_once 'ModuleInstall/ModuleInstaller.php';
/**
 * Upgrade script to remove unexisting fields from subpanel definition.
 */
class SugarUpgradeClearSubpanels extends UpgradeScript
{
    // Need to call after `1_ClearVarDefs` script.
    public $order = 4900;

    public $type = self::UPGRADE_CUSTOM;

    protected $updatedModules = array();

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        global $beanList;
        foreach ($beanList as $bean => $class) {
            $seed = BeanFactory::getBean($bean);
            if ($seed instanceof SugarBean) {
                $this->checkBean($seed);
            }
        }
        $this->rebuildExtensions($this->updatedModules);
    }

    /**
     * Check SugarBean for wrong subpanels definitions.
     * @param SugarBean $seed
     */
    protected function checkBean($seed)
    {
        $files = $this->getDefFiles($seed->module_dir);
        $defs = $this->getBeanDefs($seed);
        foreach ($files as $file) {
            $subpanel_layout = $layout_defs = array();
            include $file;
            $changed = $this->checkWidgetClass($subpanel_layout);
            $changed = $this->checkListFields($subpanel_layout, $defs) || $changed;
            if ($changed) {
                $this->updateFile($file, $subpanel_layout, 'subpanel_layout');
                $this->updatedModules[$seed->module_dir] = $seed->module_dir;
            }
            if ($this->checkSubpanelSetup($layout_defs, $defs, $seed->module_dir)) {
                $this->updateFile($file, $layout_defs, 'layout_defs');
                $this->updatedModules[$seed->module_dir] = $seed->module_dir;
            }
        }
    }

    /**
     * Check for right subpanel definition.
     * @param array $layout
     * @param array $defs
     * @param string $beanName
     * @return bool
     */
    protected function checkSubpanelSetup(&$layout, $defs, $beanName)
    {
        $needUpdate = false;
        $newLayout = $layout;
        if (!empty($newLayout[$beanName]['subpanel_setup'])) {
            foreach ($newLayout[$beanName]['subpanel_setup'] as $key => $def) {
                if (!empty($def['get_subpanel_data']) && array_key_exists($def['get_subpanel_data'], $defs)) {
                    $fld = $def['get_subpanel_data'];
                    if (!empty($defs[$fld]) && $defs[$fld]['type'] != 'link') {
                        $link = '';
                        if (!empty($defs[$fld]['link'])) {
                            $link = $defs[$fld]['link'];
                        } elseif (!empty($defs[$fld]['relationship'])) {
                            $rel = $defs[$fld]['relationship'];
                            foreach ($defs as $nm => $field) {
                                if ($field['type'] == 'link' && $field['relationship'] == $rel) {
                                    $link = $nm;
                                    break;
                                }
                            }
                        }
                        if (!empty($link)) {
                            $needUpdate = true;
                            $def['get_subpanel_data'] = $link;
                            unset($layout[$beanName]['subpanel_setup'][$key]);
                            $layout[$beanName]['subpanel_setup'][$link] = $def;
                        }
                    }
                }
            }
        }
        return $needUpdate;
    }

    /**
     * Check widget_class for standard buttons.
     * @param array $layout
     * @return bool
     */
    protected function checkWidgetClass(&$layout)
    {
        $changed = false;
        if (empty($layout['list_fields'])) {
            return $changed;
        }
        $widgets = array(
            'edit_button' => 'SubPanelEditButton',
            'remove_button' => 'SubPanelRemoveButton'
        );
        foreach ($layout['list_fields'] as $key => $field) {
            if (array_key_exists($key, $widgets) && empty($field['widget_class'])) {
                $layout['list_fields'][$key]['widget_class'] = $widgets[$key];
                $changed = true;
            }
        }
        return $changed;
    }

    /**
     * Check if fields for a subpanel exist.
     * @param array $layout
     * @param array $defs
     * @return bool
     */
    protected function checkListFields(&$layout, $defs)
    {
        $needUpdate = false;
        if (!empty($layout['list_fields'])) {
            foreach ($layout['list_fields'] as $key => $field) {
                if (empty($field['widget_class']) && empty($field['usage'])) {
                    if (!array_key_exists($key, $defs)) {
                        unset($layout['list_fields'][$key]);
                        $needUpdate = true;
                    }
                }
            }
        }
        return $needUpdate;
    }

    /**
     * Get all supbanel definitions from directory.
     * @param string $dir
     * @return array
     */
    protected function getDefFiles($dir)
    {
        $basePath = "custom/modules/{$dir}/metadata/subpanels/";
        $extPath = "custom/Extension/modules/{$dir}/Ext/Layoutdefs/";
        $files = array_merge(glob($basePath . "*.php"), glob($extPath . "*.php"));
        return $files;
    }

    /**
     * Update file with new definition
     * @param string $file
     * @param array $var
     * @param string $varName
     */
    protected function updateFile($file, $var, $varName)
    {
        $this->upgrader->backupFile($file);
        $out = "<?php\n// created: ' . date('Y-m-d H:i:s')\n";
        foreach (array_keys($var) as $key) {
            $out .= override_value_to_string_recursive2($varName, $key, $var[$key]);
        }
        sugar_file_put_contents_atomic($file, $out);
    }

    /**
     * Return field definitions of a bean.
     * Need for covering class by test.
     * @param SugarBean $bean
     * @return array
     */
    protected function getBeanDefs($bean)
    {
        return $bean->getFieldDefinitions();
    }

    /**
     * Rebuild extension cache for updated modules.
     * Need for covering class by test.
     * @param array $modules
     */
    protected function rebuildExtensions($modules)
    {
        if (!empty($modules)) {
            $mi = new ModuleInstaller();
            $mi->modules = $modules;
            $mi->rebuild_layoutdefs();
        }
    }
}
