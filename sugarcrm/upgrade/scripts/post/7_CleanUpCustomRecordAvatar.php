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

class SugarUpgradeCleanUpCustomRecordAvatar extends UpgradeScript
{
    public $order = 7100;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        //run only when upgrading from 7.x to 7.2.1
        if (version_compare($this->from_version, '7.0', '<') || version_compare($this->from_version, '7.2.1', '>=')) {
            return;
        }

        foreach (glob('custom/modules/*/clients/{base,portal}/views/record/record.php', GLOB_BRACE) as $recordFile) {
            require $recordFile;

            if (!empty($viewdefs)) {
                $module = key($viewdefs);

                //make sure header panel exists and has fields
                if (!empty($viewdefs[$module]) && !empty($viewdefs[$module]['base']) &&
                    !empty($viewdefs[$module]['base']['view']['record']) &&
                    !empty($viewdefs[$module]['base']['view']['record']['panels']) &&
                    !empty($viewdefs[$module]['base']['view']['record']['panels'][0]['fields'])
                ) {
                    $newViewdefs = $this->cleanUpAvatarField($viewdefs, $module);
                    sugar_file_put_contents_atomic(
                        $recordFile,
                        "<?php\n\n"
                        . "/* This file was updated by 7_CleanUpCustomRecordAvatar */\n"
                        . "\$viewdefs['{$module}']['base']['view']['record'] = "
                        . var_export(
                            $newViewdefs[$module]['base']['view']['record'], true)
                        . ";\n"
                    );
                }
            }
            $viewdefs = null;
        }
    }

    /**
     * Removes the `width` and `height` properties from the avatar field as they are out of date.
     *
     * Assumes that `$vdefs` contains a header panel with a list of fields.
     *
     * @param array $vdefs Custom record view definitions.
     * @param string $module Module of `record.php`.
     */
    private function cleanUpAvatarField(array $vdefs, $module)
    {
        foreach($vdefs[$module]['base']['view']['record']['panels'][0]['fields'] as $key => $headerField) {
            if (isset($headerField['type']) && $headerField['type'] === 'avatar') {
                unset($vdefs[$module]['base']['view']['record']['panels'][0]['fields'][$key]['width']);
                unset($vdefs[$module]['base']['view']['record']['panels'][0]['fields'][$key]['height']);
            }
        }
        return $vdefs;
    }
}
