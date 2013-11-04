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
$fields = array(
    'category_name',
    'discount_price',
    'tax_class',
    'mft_part_num',
    'weight'
);

$dependencies['RevenueLineItems']['read_only_fields'] = array(
    'hooks' => array("edit"),
    //Trigger formula for the dependency. Defaults to 'true'.
    'trigger' => 'true',
    'triggerFields' => array('product_template_name'),
    'onload' => true,
    //Actions is a list of actions to fire when the trigger is true
    'actions' => array(),
);

foreach ($fields as $field) {
    $dependencies['RevenueLineItems']['read_only_fields']['actions'][] = array(
        'name' => 'ReadOnly', //Action type
        //The parameters passed in depend on the action type
        'params' => array(
            'target' => $field,
            'label' => $field . '_label', //normally <field>_label
            'value' => 'not(equal($product_template_name,""))', //Formula
        ),
    );
}

/**
 * This dependency set the commit_stage to the correct value and to read only when the sales stage
 * is Closed Won (include) or Closed Lost (exclude)
 */
$dependencies['RevenueLineItems']['commit_stage_readonly_set_value'] = array(
    'hooks' => array("edit"),
    //Trigger formula for the dependency. Defaults to 'true'.
    'trigger' => 'true',
    'triggerFields' => array('sales_stage'),
    'onload' => true,
    //Actions is a list of actions to fire when the trigger is true
    'actions' => array(
        array(
            'name' => 'ReadOnly', //Action type
            //The parameters passed in depend on the action type
            'params' => array(
                'target' => 'commit_stage',
                'label' => 'commit_stage_label', //normally <field>_label
                'value' => 'isInList($sales_stage, createList("Closed Won", "Closed Lost"))', //Formula
            ),
        ),
        array(
            'name' => 'SetValue', //Action type
            //The parameters passed in depend on the action type
            'params' => array(
                'target' => 'commit_stage',
                'label' => 'commit_stage_label', //normally <field>_label
                'value' => 'ifElse(equal($sales_stage, "Closed Won"), "include",
                    ifElse(equal($sales_stage, "Closed Lost"), "exclude", $commit_stage))', //Formula
            ),
        )
    ),
);

/**
 * This dependency set the best and worst values to equal likely when the sales stage is
 * set to closed won.
 */
$dependencies['RevenueLineItems']['best_worst_sales_stage_read_only'] = array(
    'hooks' => array("edit"),
    //Trigger formula for the dependency. Defaults to 'true'.
    'trigger' => 'true',
    'triggerFields' => array('sales_stage'),
    'onload' => true,
    //Actions is a list of actions to fire when the trigger is true
    'actions' => array(
        array(
            'name' => 'ReadOnly', //Action type
            //The parameters passed in depend on the action type
            'params' => array(
                'target' => 'best_case',
                'label' => 'best_case_label', //normally <field>_label
                'value' => 'isInList($sales_stage, createList("Closed Won"))', //Formula
            ),
        ),
        array(
            'name' => 'ReadOnly', //Action type
            //The parameters passed in depend on the action type
            'params' => array(
                'target' => 'worst_case',
                'label' => 'worst_case_label', //normally <field>_label
                'value' => 'isInList($sales_stage, createList("Closed Won"))', //Formula
            ),
        ),
        array(
            'name' => 'SetValue', //Action type
            //The parameters passed in depend on the action type
            'params' => array(
                'target' => 'best_case',
                'label' => 'best_case_label',
                'value' => 'ifElse(isInList($sales_stage, createList("Closed Won")), $likely_case, $best_case)',
            ),
        ),
        array(
            'name' => 'SetValue', //Action type
            //The parameters passed in depend on the action type
            'params' => array(
                'target' => 'worst_case',
                'label' => 'worst_case_label',
                'value' => 'ifElse(isInList($sales_stage, createList("Closed Won")), $likely_case, $worst_case)',
            ),
        ),
    )
);

$dependencies['RevenueLineItems']['likely_case_copy_when_closed'] = array(
    'hooks' => array("edit"),
    //Trigger formula for the dependency. Defaults to 'true'.
    'trigger' => 'true',
    'triggerFields' => array('likely_case'),
    'onload' => true,
    //Actions is a list of actions to fire when the trigger is true
    'actions' => array(
        array(
            'name' => 'SetValue', //Action type
            //The parameters passed in depend on the action type
            'params' => array(
                'target' => 'best_case',
                'label' => 'best_case_label',
                'value' => 'ifElse(isInList($sales_stage, createList("Closed Won")), $likely_case, $best_case)',
            ),
        ),
        array(
            'name' => 'SetValue', //Action type
            //The parameters passed in depend on the action type
            'params' => array(
                'target' => 'worst_case',
                'label' => 'worst_case_label',
                'value' => 'ifElse(isInList($sales_stage, createList("Closed Won")), $likely_case, $worst_case)',
            ),
        ),
    )
);
