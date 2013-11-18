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
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once('modules/DynamicFields/templates/Fields/TemplateDecimal.php');
class TemplateCurrencyBaseRate extends TemplateDecimal
{
    public $len = 26;
    public $precision = 6;

    /**
     * return the field defs
     *
     * @return array
     */
    public function get_field_def()
    {
        $def = parent::get_field_def();
        $def['type'] = $this->type;
        $def['vname'] = 'LBL_CURRENCY_RATE';
        $def['label'] = 'LBL_CURRENCY_RATE';
        $def['studio'] = false;
        return $def;
    }

    /**
     * Save the field if one doesn't already exist
     *
     * @param DynamicField $df
     */
    public function save($df)
    {
        if (!$df->fieldExists($this->name)) {
            parent::save($df);
        }
    }

    /**
     * Delete the field is a currency field is no loner on the module
     *
     * @param DynamicField $df
     */
    public function delete($df)
    {
        if (!$df->fieldExists(null, 'currency')) {
            parent::delete($df);
        }
    }
}
