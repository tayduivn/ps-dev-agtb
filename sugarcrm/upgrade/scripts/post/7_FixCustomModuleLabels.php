<?php
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

/**
 * Fix missing labels for instance custom modules
 */
class SugarUpgradeFixCustomModuleLabels extends UpgradeScript
{
    public $order = 7600;
    public $type = self::UPGRADE_CUSTOM;

    protected $missingLabels =
        array(
            'LNK_IMPORT_{module_name}',
        );

    public function run()
    {
        // Only run this when coming from a version lower than 7.2.0
        if (version_compare($this->from_version, '7.2', '>=')) {
            return;
        }

        // Find all the classes we want to convert.
        $customModules = $this->getCustomModules();

        foreach ($customModules as $moduleName) {

            $path = $this->getModuleLangPath($moduleName);

            if (file_exists($path)) {

                $mod_strings = array();
                require $path;

                $labels = $this->compileLabels($moduleName, $this->missingLabels);
                $missingLabels = array_diff($labels, array_keys($mod_strings));

                if (!empty($missingLabels)) {
                    $this->upgrader->log(
                        'FixCustomModuleLabels: Missing import labels for '
                        . $moduleName . ' module - ' . var_export($missingLabels, true)
                    );

                    $header = file_get_contents('modules/ModuleBuilder/MB/header.php');
                    $translations = $this->translateLabels($missingLabels, $mod_strings, $moduleName);

                    $this->upgrader->backupFile($path);
                    write_array_to_file('mod_strings', $translations, $path, 'w', $header);

                    $this->upgrader->log('FixCustomModuleLabels: Module ' . $moduleName . '. Saving Complete');
                }
            }
        }

        return true;
    }

    /**
     * Get path to module language file
     *
     * @param $module
     * @return string
     */
    protected function getModuleLangPath($module)
    {
        return 'modules/' . $module . '/language/en_us.lang.php';
    }

    /**
     * Get SugarCRM instance custom modules
     *
     * @return array
     */
    protected function getCustomModules()
    {
        // Find all the classes we want to convert.
        $customModules = array();
        $customFiles = glob('modules/*/*_sugar.php', GLOB_NOSORT);

        foreach ($customFiles as $customFile) {
            $moduleName = str_replace('_sugar', '', pathinfo($customFile, PATHINFO_FILENAME));
            $customModules[] = $moduleName;
        }

        return $customModules;
    }

    /**
     * Return labels for search
     *
     * @param $moduleName
     * @param string|array $labels
     * @return mixed
     */
    public function compileLabels($moduleName, $labels)
    {
        return str_replace('{module_name}', strtoupper($moduleName), $labels);
    }

    /**
     * Compile translations array.
     * Put label translation logic there
     *
     * @param array $mod_strings
     * @param string $moduleName
     * @return array
     */
    protected function compileTranslations($mod_strings, $moduleName)
    {
        return array(
                'LNK_IMPORT_' . strtoupper($moduleName) => translate('LBL_IMPORT') . " " . $mod_strings['LBL_MODULE_NAME'],
               );
    }

    /**
     * Translate missing labels for module
     *
     * @param $labels
     * @param $mod_strings
     * @param $moduleName
     * @return mixed
     */
    public function translateLabels($labels, $mod_strings, $moduleName)
    {
        $knownTranslations = $this->compileTranslations($mod_strings, $moduleName);

        foreach ($labels as $label) {
            if (!isset($knownTranslations[$label])) {
                $this->upgrader->log('FixCustomModuleLabels: unable to translate label ' . $label);
            } else {
                $mod_strings[$label] = $knownTranslations[$label];
            }
        }

        return $mod_strings;
    }
}
