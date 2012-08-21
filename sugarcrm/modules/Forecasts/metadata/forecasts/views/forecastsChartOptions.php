<?php
$viewdefs['Forecasts']['forecasts']['view']['forecastsChartOptions'] = array(
	'panels' => array(
		0 => array(
			'label' => 'LBL_CHART_OPTIONS',
			'fields' => array(
				0 => array(
					'name' => 'group_by',
					'label' => 'LBL_GROUP_BY',
                    'type' => 'enum',
                    'options' => 'forecasts_chart_options_group',
                    'default' => true,
					'enabled' => true,
				),
                1 => array(
                    'name' => 'dataset',
                    'label' => 'LBL_DATA_SET',
                    'type' => 'enum',
                    'options' => 'forecasts_chart_options_dataset',
                    'default' => true,
                    'enabled' => true,
                )
			),
		),
	),
);