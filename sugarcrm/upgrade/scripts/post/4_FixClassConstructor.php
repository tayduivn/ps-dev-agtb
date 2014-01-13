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

require_once 'modules/ModuleBuilder/controller.php';

/**
 * Class SugarUpgradeFixClassConstructor
 *
 * Fix custom module classes to be rewritten to use the proper __construct()
 * form instead of class names.
 */
class SugarUpgradeFixClassConstructor extends UpgradeScript
{
    public $order = 4000;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        // only run this when coming from a version lower than 7.2.0
        if (version_compare($this->from_version, '7.2.0', '>=')) {
            return;
        }

        $mB = new ModuleBuilder();
        $mB->getPackages();

        foreach ($mB->packages as $package) {
            $this->log("FixClassConstructor: Found a custom package {$package->name}");
            foreach ($package->modules as $module) {
                $this->replaceCustomModuleClasses($module);
            }
        }
        ob_end_clean();
    }

    /**
     * Rebuild the custom module classes so they have the the proper
     * __construct() instead of class names.
     *
     * @param MBModule $mbModule ModuleBuilder Module to be replaced.
     *
     * @see MBModule::createClasses() for duplication of this code.
     * @todo refactor MBModule::createClasses() to be able to reuse code
     * (tracked by SC-2279).
     */
    private function replaceCustomModuleClasses($mbModule)
    {
        $class = array();
        $class['name'] = $mbModule->key_name;

        if (!file_exists('modules/' . $class['name'] . '/' . $class['name'] . '_sugar.php')) {
            return false;
        }

        $class['table_name'] = strtolower($class['name']);
        $class['extends'] = 'Basic';
        $class['requires'] = array();

        $class['team_security'] = !empty($mbModule->config['team_security']);

        if (empty($mbModule->config['audit'])) {
            $class['audited'] = 'false';
        } else {
            $class['audited'] = 'true';
        }

        if (empty($mbModule->config['activity_enabled'])) {
            $class['activity_enabled'] = 'false';
        } else {
            $class['activity_enabled'] = 'true';
        }

        if (empty($mbModule->config['acl'])) {
            $class['acl'] = 'false';
        } else {
            $class['acl'] = 'true';
        }

        $class['templates'] = "'basic'";
        foreach ($mbModule->iTemplate as $template) {
            if (!empty($mbModule->config[$template])) {
                $class['templates'] .= ",'$template'";
            }
        }

        foreach ($mbModule->config['templates'] as $template => $a) {
            if ($template == 'basic') {
                continue;
            }
            $class['templates'] .= ",'$template'";
            $class['extends'] = ucFirst($template);
            $class['requires'][] = 'include/SugarObjects/templates/' . $template . '/' . ucfirst($template) . '.php';
        }
        $class['importable'] = $mbModule->config['importable'];
        $mbModule->mbvardefs->updateVardefs();
        $class['fields'] = $mbModule->mbvardefs->vardefs['fields'];
        $class['fields_string'] = var_export_helper($mbModule->mbvardefs->vardef['fields']);
        $relationship = array();
        $class['relationships'] = var_export_helper($mbModule->mbvardefs->vardef['relationships']);
        $smarty = new Sugar_Smarty ();
        $smarty->left_delimiter = '{{';
        $smarty->right_delimiter = '}}';
        $smarty->assign('class', $class);

        //write sugar generated class
        $this->log("FixClassConstructor: Replace {$class['name']}_sugar.php for module: {$mbModule->key_name}");
        $content = $smarty->fetch('modules/ModuleBuilder/tpls/MBModule/Class.tpl');
        sugar_file_put_contents_atomic('modules/' . $class['name'] . '/' . $class['name'] . '_sugar.php', $content);
    }
}
