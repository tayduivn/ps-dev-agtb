<?php
$viewdefs['Forecasts']['base']['view']['testView'] = array(
	'panels' =>
	array(
		array(
			'label' => 'Test View',
			'fields' =>
			array(
                array(
                    'name' => 'test_range_0',
                    'label' => 'Range 0',
                    'type' => 'range',
                    'view' => 'edit',
                    'sliderType' => 'upper',
                    'default' => true,
                    'enabled' => true,
                ),
                array(
                    'name' => 'test_range_1',
                    'label' => 'Range 1',
                    'type' => 'range',
                    'view' => 'edit',
                    'sliderType' => 'connected',
                    'default' => true,
                    'enabled' => true,
                ),
                array(
                    'name' => 'test_range_2',
                    'label' => 'Range 2',
                    'type' => 'range',
                    'view' => 'edit',
                    'sliderType' => 'lower',
                    'default' => true,
                    'enabled' => true,
                ),
                array(
                    'name' => 'test_range_3',
                    'label' => 'Range 3',
                    'type' => 'range',
                    'view' => 'edit',
                    'sliderType' => 'single',
                    'default' => true,
                    'enabled' => true,
                ),
                array(
                    'name' => 'test_range_4',
                    'label' => 'Range 4',
                    'type' => 'range',
                    'view' => 'edit',
                    'sliderType' => 'double',
                    'default' => true,
                    'enabled' => true,
                ),
			),
		),
	),
);