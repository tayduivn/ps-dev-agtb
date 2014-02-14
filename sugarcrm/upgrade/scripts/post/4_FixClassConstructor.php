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
        // Only run this when coming from a version lower than 7.2.0
        if (version_compare($this->from_version, '7.2.0', '>=')) {
            return;
        }

        // Find all the classes we want to convert.
        $customModules = array();
        foreach (glob('modules/*/*_sugar.php') as $customFile) {
            $moduleName = str_replace('_sugar', '', pathinfo($customFile, PATHINFO_FILENAME));
            $customModules[] = $moduleName;
        }
        $customModules = array_flip($customModules);

        // Go through all the modules using the ModuleBuilder
        $mB = new ModuleBuilder();
        $mB->getPackages();
        foreach ($mB->packages as $package) {
            $this->log("FixClassConstructor: Found a custom package {$package->name}");
            foreach ($package->modules as $mbModule) {
                if (!isset($customModules[$mbModule->key_name])) {
                    continue;
                }
                $this->replaceCustomModuleClasses($mbModule);
                unset($customModules[$mbModule->key_name]);
            }
        }
        $customModules = array_flip($customModules);

        // Treat modules that have not been found by the ModuleBuilder
        foreach ($customModules as $moduleName) {
            $this->log("FixClassConstructor: Found a custom module {$moduleName} not recognized by ModuleBuilder");
            $this->replaceCustomModuleClassesByReflection($moduleName);
        }

        // TODO: ModuleBuilder outputs some blank lines. Verify why.
        ob_end_clean();
    }

    /**
     * Rebuild the custom module classes so they have the the proper
     * __construct() instead of class names.
     *
     * This method uses the ModuleBuilder class to populate the class template.
     *
     * @param MBModule $mbModule ModuleBuilder Module to be replaced.
     *
     * @see MBModule::createClasses() for duplication of this code.
     * @todo refactor MBModule::createClasses() to be able to reuse code
     * (tracked by SC-2279).
     */
    private function replaceCustomModuleClasses($mbModule)
    {
        $moduleName = $mbModule->key_name;

        $parentClass = 'Basic';
        foreach ($mbModule->config['templates'] as $template => $a) {
            if ($template == 'basic') {
                continue;
            }
            $parentClass = ucFirst($template);
        }

        $mbModule->mbvardefs->updateVardefs();
        $fields = $mbModule->mbvardefs->vardefs['fields'];

        $params = array(
            'isImportable' => !empty($mbModule->config['importable']),
            'teamSecurityEnabled' => !empty($mbModule->config['team_security']),
            'acl' => !empty($mbModule->config['acl']),
        );

        $content = $this->populateClassTemplate($moduleName, $parentClass, $fields, $params);
        $this->writeCustomClass($moduleName, $content);
    }

    /**
     * Rebuild the custom module classes so they have the the proper
     * __construct() instead of class names.
     *
     * This method uses ReflectionClass to populate the class template.
     *
     * @param string $moduleName The module key name.
     */
    private function replaceCustomModuleClassesByReflection($moduleName)
    {
        $className = $moduleName . '_sugar';

        $reflectionClass = new ReflectionClass($className);
        $parentClass = get_parent_class($className);

        $fields = array();
        $notVardefs = array('new_schema', 'module_dir', 'object_name', 'table_name', 'importable');
        foreach ($reflectionClass->getProperties() as $property) {
            if ($property->class !== $className) {
                continue;
            }
            $name = $property->name;
            if (in_array($name, $notVardefs)) {
                continue;
            }
            $fields[$name] = $name;
        }

        $isImportable = $reflectionClass->getProperty('importable')->getValue(new $className);

        $teamSecurityEnabled = true;
        $disable_row_level_security = $reflectionClass->getProperty('disable_row_level_security');
        if (!empty($disable_row_level_security)) {
            $teamSecurityEnabled = $disable_row_level_security->class !== $className;
        }

        $acl = false;
        $beanImplements = $reflectionClass->getMethod('bean_implements');
        if (!empty($beanImplements)) {
            $acl = $beanImplements->class === $className;
        }

        $params = array(
            'isImportable' => $isImportable,
            'teamSecurityEnabled' => $teamSecurityEnabled,
            'acl' => $acl,
        );

        $content = $this->populateClassTemplate($moduleName, $parentClass, $fields, $params);
        $this->writeCustomClass($moduleName, $content);
    }

    /**
     * Populate the class template.
     *
     * @param string $moduleName The module name.
     * @param array $parentClass The name of the extension class.
     * @param array $fields The list of fields of this module.
     * @param array $params The list of boolean parameters.
     * Expects `isImportable`, `teamSecurityEnabled` and `acl` to be defined.
     *
     * @return string The file content, ready to be written.
     */
    private function populateClassTemplate($moduleName, $parentClass, $fields, $params)
    {
        $class = array();
        $class['name'] = $moduleName;
        $class['table_name'] = strtolower($moduleName);

        $class['requires'] = array();
        if ($parentClass !== 'Basic') {
            $template = strtolower($parentClass);
            $class['requires'][] = 'include/SugarObjects/templates/' . $template . '/' . $parentClass . '.php';
        }

        $class['extends'] = $parentClass;

        $class['fields'] = $fields;

        $class['team_security'] = $params['teamSecurityEnabled'];
        $class['acl'] = $params['acl'];
        $class['importable'] = $params['isImportable'];

        $smarty = new Sugar_Smarty();
        $smarty->left_delimiter = '{{';
        $smarty->right_delimiter = '}}';
        $smarty->assign('class', $class);
        $content = $smarty->fetch('modules/ModuleBuilder/tpls/MBModule/Class.tpl');

        return $content;
    }

    /**
     * Override the existing file.
     *
     * @param string $moduleName The module name.
     * @param string $content The content of the file.
     */
    private function writeCustomClass($moduleName, $content)
    {
        //write sugar generated class
        $this->log("FixClassConstructor: Replace {$moduleName}_sugar.php for module: {$moduleName}");
        sugar_file_put_contents_atomic('modules/' . $moduleName . '/' . $moduleName . '_sugar.php', $content);
    }
}
