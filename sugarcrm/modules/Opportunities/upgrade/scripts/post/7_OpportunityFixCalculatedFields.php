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
 * Copyright (C) 2004-2014 SugarCRM Inc.  All rights reserved.
 */
/**
 * Update fields that have been modified to be calculated.
 */
class SugarUpgradeOpportunityFixCalculatedFields extends UpgradeScript
{
    public $order = 7000;
    public $type = self::UPGRADE_CUSTOM;

    public function run()
    {
        if ((!$this->toFlavor('ent') && !$this->toFlavor('ult')) || !version_compare($this->from_version, '7.0', '<')) {
            return;
        }

        // get the get_widget helper and the StandardField Helper
        require_once('modules/DynamicFields/FieldCases.php');
        require_once('modules/ModuleBuilder/parsers/StandardField.php');

        // we are working with opportunities
        $module = 'Opportunities';
        $bean = BeanFactory::getBean('Opportunities');

        // the field set we need
        $fields = array(
            'best_case',
            'amount',
            'worst_case',
            'date_closed'
        );

        // loop over each field
        foreach($fields as $field) {
            // get the field defs
            $field_defs = $bean->getFieldDefinition($field);
            // load the field type up
            $f = get_widget($field_defs['type']);

            // populate the row from the vardefs that were loaded
            $f->populateFromRow($field_defs);
            // lets make sure that the calculated is true
            $f->calculated = true;

            // now lets save, since these are OOB field, we use StandardField
            $df = new StandardField($module);
            $df->setup($bean);
            $f->module = $bean;
            $f->save($df);
        }
    }
}
