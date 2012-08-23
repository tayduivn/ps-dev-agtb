<?php
$viewdefs['Forecasts']['forecasts']['view']['forecastsTimeframes'] = array(
	'panels' => array(
		0 => array(
			'fields' => array(
				0 => array(
					'name' => 'timeframes',
					'label' => 'LBL_FORECAST_PERIOD',
                    'type' => 'enum',
                    // options are set dynamically in the view
                    'default' => true,
					'enabled' => true,
				),
            ),
        ),
    ),
);