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

require_once 'ModuleInstall/ModuleInstaller.php';

/**
 * Upgrade script to clear hooks with wrong references.
 */
class SugarUpgradeClearHooks extends UpgradeScript
{
    public $order = 9400;

    public $type = self::UPGRADE_CUSTOM;

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $hooksModules = $this->findHookFiles();
        //Check modules hooks.
        foreach ($hooksModules['hooks'] as $file) {
            $this->testHooks($file);
        }
        //Check extension hooks.
        foreach ($hooksModules['ext'] as $file) {
            $this->testHooks($file);
        }
        if (!empty($hooksModules['ext'])) {
            $mi = new ModuleInstaller();
            $mi->rebuild_logichooks();
        }
    }

    /**
     * Find all hooks files.
     * @return array
     */
    protected function findHookFiles()
    {
        $modules = array(
            'hooks' => array(),
            'ext' => array(),
        );
        $path1 = "custom/modules/*/logic_hooks.php";
        $path2 = "custom/modules/logic_hooks.php";
        $path3 = "custom/Extension/modules/*/Ext/LogicHooks/*.php";
        $path4 = "custom/Extension/application/Ext/LogicHooks/*.php";

        $modules['hooks'] = array_merge(glob($path1), glob($path2));
        $modules['ext'] = array_merge(glob($path3), glob($path4));

        return $modules;
    }

    /**
     * Rewrite logic hook's file with new hooks.
     * @param String $hook_file
     * @param array $hooks
     */
    protected function rewriteHookFile($hook_file, $hooks)
    {
        $this->log("**** Rewrite hooks for {$hook_file}");
        $this->upgrader->backupFile($hook_file);
        if (empty($hooks)) {
            unlink($hook_file);
        } else {
            $out = "<?php\n";
            foreach ($hooks as $event_array => $event) {
                foreach ($event as $elements) {
                    $out .= "\$hook_array['{$event_array}'][] = array(";
                    foreach ($elements as $el) {
                        $out .= var_export($el, true) . ',';
                    }
                    $out .= ");\n";
                }
            }
            file_put_contents($hook_file, $out);
        }
    }

    /**
     * Check logic hook file for bad definitions.
     * @param String $hook_file
     */
    protected function testHooks($hook_file)
    {
        $needRewrite = false;
        $hook_array = array();
        include $hook_file;
        foreach ($hook_array as $k => $hooks) {
            foreach ($hooks as $j => $hook) {
                $validHooks = false;
                if (count($hook) >= 5 && file_exists($hook[2])) {
                    include_once $hook[2];
                    if (class_exists($hook[3]) && method_exists($hook[3], $hook[4])) {
                        $validHooks = true;
                    }
                }
                if (!$validHooks) {
                    $this->log("DELETE bad hook '{$hook[1]}' in '{$hook_file}'");
                    $needRewrite = true;
                    unset($hook_array[$k][$j]);
                }
            }
            if (empty($hook_array[$k])) {
                unset($hook_array[$k]);
            }
        }
        if ($needRewrite) {
            $this->rewriteHookFile($hook_file, $hook_array);
        }
    }
}
