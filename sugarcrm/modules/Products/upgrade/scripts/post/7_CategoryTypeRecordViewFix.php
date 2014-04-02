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
 * Copyright  2004-2014 SugarCRM Inc.  All rights reserved.
 */

class SugarUpgradeCategoryTypeRecordViewFix extends UpgradeScript
{
    public $order = 7500;
    public $type = self::UPGRADE_CUSTOM;
    public $version = '7.2.0';

    protected $file = 'custom/modules/Products/clients/base/views/record/record.php';
    protected $field_rename_map = array(
        'type_id' => 'type_name',
        'category_id' => 'category_name'
    );

    public function run()
    {
        // if we are coming from anything newer than 7, just bail
        if (version_compare($this->from_version, '7.0.0', '>')) {
            return;
        }

        if (!SugarAutoLoader::fileExists($this->file)) {
            # if we don't have a custom file, then bail
            return;
        }
        $viewdefs = null;

        include $this->file;

        if (!empty($viewdefs)) {
            $viewdefs = $this->fixFieldName($viewdefs);
            sugar_file_put_contents($this->file, "<?php\n\n \$viewdefs = " . var_export($viewdefs, true) . ";\n");

        }

        $viewdefs = null;
    }

    /**
     * loop over view, find labels that match pattern and remove them
     * @param array $arr
     * @return array return array with removed
     */
    public function fixFieldName($arr)
    {
        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                $arr[$key] = $this->fixFieldName($val);
            } elseif ($key === 'name' && isset($this->field_rename_map[$val])) {
                $arr[$key] = $this->field_rename_map[$val];
            }
        }
        return $arr;
    }
}
