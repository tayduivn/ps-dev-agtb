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

class SugarUpgradeFixCustomRequiredFields extends UpgradeScript
{
    public $order = 7150;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        if (!version_compare($this->from_version, '7.6', '<')) {
            return;
        }

        $customFieldFiles = $this->getCustomFieldFiles();
        foreach ($customFieldFiles as $file) {
            if (is_dir($file)) {
                continue;
            }
            $dictionary = array();
            require $file;

            if (empty($dictionary)) {
                continue;
            }
            $module = key($dictionary);

            if (empty($dictionary[$module]['fields'])) {
                continue;
            }
            $fields = array_keys($dictionary[$module]['fields']);

            if (empty($dictionary[$module]['fields']['date_entered']['required']) &&
                empty($dictionary[$module]['fields']['date_modified']['required'])
            ) {
                continue;
            }
            else {
                // date_entered & date_modified are read only fields, set the required to false if set
                if (!empty($dictionary[$module]['fields']['date_entered']['required'])) {
                    $dictionary[$module]['fields']['date_entered']['required'] = false;
                }
                if (!empty($dictionary[$module]['fields']['date_modified']['required'])) {
                    $dictionary[$module]['fields']['date_modified']['required'] = false;
                }
            }

            $strToFile = "<?php\n\n";
            foreach ($fields as $field) {
                foreach ($dictionary[$module]['fields'][$field] as $key => $value) {
                    $strToFile .= "\$dictionary['{$module}']['fields']['{$field}']['{$key}'] = " . var_export(
                            $value,
                            true
                        ) . ";\n";
                }
            }
            $this->upgrader->backupFile($file);
            sugar_file_put_contents_atomic($file, $strToFile);
        }
    }

    /**
     * Return custom field paths.
     *
     * @return array
     */
    protected function getCustomFieldFiles()
    {
        return glob('custom/Extension/modules/*/Ext/Vardefs/*');
    }
}
