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

require_once('modules/DynamicFields/templates/Fields/TemplateCurrencyId.php');
require_once('modules/DynamicFields/templates/Fields/TemplateCurrencyBaseRate.php');
require_once('modules/DynamicFields/templates/Fields/TemplateRange.php');

class TemplateCurrency extends TemplateRange
{
    public $max_size = 25;
    public $len = 26;
    public $precision = 6;
    public $type = 'currency';

    public function delete($df)
    {
        parent::delete($df);
        //currency id
        $currency_id = new TemplateCurrencyId();
        $currency_id->name = 'currency_id';
        $currency_id->delete($df);

        //base_rate
        $base_rate = new TemplateCurrencyBaseRate();
        $base_rate->name = 'base_rate';
        $base_rate->delete($df);
    }

    public function save($df)
    {
        //the currency field
        $this->default = unformat_number($this->default);
        $this->default_value = $this->default;
        $this->related_fields = array(
            'currency_id',
            'base_rate'
        );
        parent::save($df);

        //currency id
        $currency_id = new TemplateCurrencyId();
        $currency_id->name = 'currency_id';
        $currency_id->vname = 'LBL_CURRENCY';
        $currency_id->label = $currency_id->vname;
        $currency_id->save($df);
        $df->addLabel($currency_id->vname);

        //base_rate
        $base_rate = new TemplateCurrencyBaseRate();
        $base_rate->name = 'base_rate';
        $base_rate->label = 'LBL_CURRENCY_RATE';
        $base_rate->save($df);

    }

    public function get_field_def()
    {
        $def = parent::get_field_def();
        $def['precision'] = (!empty($this->precision)) ? $this->precision : 6;
        $def['related_fields'] = array('currency_id', 'base_rate');
        return $def;
    }

    function get_db_type()
    {
        $precision = (!empty($this->precision)) ? $this->precision : 6;
        $len = (!empty($this->len)) ? $this->len : 26;
        return " " . sprintf($GLOBALS['db']->getColumnType("decimal_tpl"), $len, $precision);
    }
}
