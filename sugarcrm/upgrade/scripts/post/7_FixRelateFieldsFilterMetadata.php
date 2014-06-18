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

class SugarUpgradeFixRelateFieldsFilterMetadata extends UpgradeScript
{
    public $order = 7100;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        //run only when upgrading from 7.x to 7.2.1
        if (version_compare($this->from_version, '7.0', '<') || version_compare($this->from_version, '7.2.1', '>=')) {
            return;
        }

        $this->cleanUpField('Cases', array('account_name'));
        $this->cleanUpField('Contacts', array('account_name'));
        $this->cleanUpField('Notes', array('contact_name'));
        $this->cleanUpField('Opportunities', array('account_name'));
        $this->cleanUpField('Quotes', array('account_name'));
        $this->cleanUpField(
            'RevenueLineItems',
            array(
                'account_name',
                'opportunity_name',
                'product_template_name',
                'category_name'
            )
        );
        $this->cleanUpField('Tasks', array('contact_name'));
    }

    /**
     * Removes fields' filter definition.
     *
     * More precisely we need to remove the `dbFields`, `type` and `vname`
     * properties from the filter definition of `relate` type fields.
     *
     * @param string $module The module name.
     * @param array $fields The list of fields to fix.
     */
    private function cleanUpField($module, $fields)
    {
        $file = 'custom/modules/' . $module . '/clients/base/filters/default/default.php';
        if (!file_exists($file)) {
            return;
        }

        $viewdefs = null;
        require $file;

        foreach ($fields as $fieldName) {
            if (isset($viewdefs[$module]['base']['filter']['default']['fields'][$fieldName])) {
                $viewdefs[$module]['base']['filter']['default']['fields'][$fieldName] = array();
            }
        }

        sugar_file_put_contents_atomic(
            $file,
            "<?php\n\n"
            . "/* This file was updated by 7_FixRelateFieldsFilterMetadata */\n"
            . "\$viewdefs['{$module}']['base']['filter']['default'] = "
            . var_export($viewdefs[$module]['base']['filter']['default'], true)
            . ";\n"
        );
    }
}
