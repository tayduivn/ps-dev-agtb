<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
$fields = array(
    'category_name',
    'list_price',
    'cost_price',
    'tax_class',
    'mft_part_num',
    'weight'
);

$serviceFieldDefaults = array(
    'service_start_date' => 'now()',
    'service_duration_value' => '1',
    'service_duration_unit' => '"year"',
);

$dependencies['Products']['read_only_fields'] = array(
    'hooks' => array("edit"),
    //Trigger formula for the dependency. Defaults to 'true'.
    'trigger' => 'true',
    'triggerFields' => array('product_template_id'),
    'onload' => true,
    //Actions is a list of actions to fire when the trigger is true
    'actions' => array(),
);

foreach ($fields as $field) {
    $dependencies['Products']['read_only_fields']['actions'][] = array(
        'name' => 'ReadOnly', //Action type
        //The parameters passed in depend on the action type
        'params' => array(
            'target' => $field,
            'label' => $field . '_label', //normally <field>_label
            'value' => 'not(equal($product_template_id,""))', //Formula
        ),
    );
}

// Handle the dependencies when the 'service' field is checked/unchecked
$serviceFieldActions = array();
foreach ($serviceFieldDefaults as $field => $defaultValue) {
    $serviceFieldActions[] = array(
        'name' => 'SetRequired',
        'params' => array(
            'target' => $field,
            'value' => 'equal($service, "1")',
        ),
    );
    $serviceFieldActions[] = array(
        'name' => 'SetValue',
        'params' => array(
            'target' => $field,
            'value' => 'ifElse(
                equal($service, "1"),
                ifElse(
                    equal($' . $field . ', ""),
                    '. $defaultValue .',
                    $'. $field .'
                ),
                "")',
        ),
    );
}

$serviceFieldActions[] = array(
    'name' => 'ReadOnly',
    'params' => array(
        'target' => 'service_start_date',
        'value' => 'equal($service, "0")',
    ),
);
$serviceFieldActions[] = array(
    'name' => 'ReadOnly',
    'params' => array(
        'target' => 'service_duration_value',
        'value' => 'or(equal($service, "0"),equal($has_service_template,true))',
    ),
);
$serviceFieldActions[] = array(
    'name' => 'ReadOnly',
    'params' => array(
        'target' => 'service_duration_unit',
        'value' => 'or(equal($service, "0"),equal($has_service_template,true))',
    ),
);

// 'renewable' field is similar to the other service fields, but never required
$serviceFieldActions[] = array(
    'name' => 'ReadOnly',
    'params' => array(
        'target' => 'renewable',
        'value' => 'equal($service, "0")',
    ),
);
$serviceFieldActions[] = array(
    'name' => 'SetValue',
    'params' => array(
        'target' => 'renewable',
        'value' => 'ifElse(
                equal($service, "1"),
                $renewable,
                "0")',
    ),
);
$dependencies['Products']['handle_service_dependencies'] = array(
    'hooks' => array('edit'),
    'trigger' => 'true',
    'triggerFields' => array('service'),
    'onload' => true,
    'actions' => $serviceFieldActions,
);

$dependencies['Products']['service_template_read_only_fields'] = [
    'hooks' => ['edit'],
    'trigger' => 'true',
    'triggerFields' => ['has_service_template'],
    'onload' => true,
    'actions' => [
        [
            'name' => 'ReadOnly',
            'params' => [
                'target' => 'service',
                'label' => 'service_label',
                'value' => 'equal($has_service_template,true)',
            ],
        ], [
            'name' => 'ReadOnly',
            'params' => [
                'target' => 'service_duration_unit',
                'label' => 'service_duration_unit_label',
                'value' => 'equal($has_service_template,true)',
            ],
        ], [
            'name' => 'ReadOnly',
            'params' => [
                'target' => 'service_duration_value',
                'label' => 'service_duration_value_label',
                'value' => 'equal($has_service_template,true)',
            ],
        ],
    ],
];
