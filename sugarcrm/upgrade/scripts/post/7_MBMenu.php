<?php

/**
 * If MB module does not have menu, create one
 */
class SugarUpgradeMBMenu extends UpgradeScript
{
    public $order = 7200;

    /**
     * Add default menu for module
     * @param string $module
     */
    protected function addMenu($moduleName)
    {
        $menu = array();
        // Create default menu for the module
        $menu[] = array(
                'route' => "#$moduleName/create",
                'label' => 'LNK_NEW_RECORD',
                'acl_action' => 'create',
                'acl_module' => $moduleName,
                'icon' => 'icon-plus',
        );
        $menu[] = array(
                'route' => "#$moduleName",
                'label' => 'LNK_LIST',
                'acl_action' => 'list',
                'acl_module' => $moduleName,
                'icon' => 'icon-reorder',
        );
        $menu[] = array(
                'route' => '#bwc/index.php?' . http_build_query(
                        array(
                                'module' => 'Import',
                                'action' => 'Step1',
                                'import_module' => $moduleName,
                        )
                ),
                'label' => 'LNK_IMPORT_'.strtoupper($moduleName),
                'acl_action' => 'import',
                'acl_module' => $moduleName,
                'icon' => 'icon-upload',
        );
        $content = <<<END
<?php
/* Created by SugarUpgrader for module $moduleName */
\$viewdefs['$moduleName']['base']['menu']['header'] =
END;
        $content .= var_export($menu, true) . ";\n";
        $this->ensureDir("modules/$moduleName/clients/base/menus/header");
        $this->putFile("modules/$moduleName/clients/base/menus/header/header.php", $content);
        $this->log("Added default menu file for $moduleName");
    }

    public function run()
    {
        if(empty($this->upgrader->state['MBModules'])) return;

        foreach($this->upgrader->state['MBModules'] as $moduleName) {
            if(!file_exists("modules/$moduleName")) continue;
            if(!file_exists("modules/$moduleName/clients/base/menus/header/header.php") && !file_exists("custom/modules/$moduleName/clients/base/menus/header/header.php")) {
                $this->addMenu($moduleName);
            }
        }

        // Do it also for bwcModules since some of them may not have Menu.php and we need it
        foreach ($GLOBALS['bwcModules'] as $moduleName) {
            if(!file_exists("modules/$moduleName")) continue;
            if(!file_exists("modules/$moduleName/clients/base/menus/header/header.php") && !file_exists("custom/modules/$moduleName/clients/base/menus/header/header.php")) {
                $this->addMenu($moduleName);
            }
        }
    }
}
