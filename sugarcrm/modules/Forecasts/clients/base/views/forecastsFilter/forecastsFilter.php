<?php
$viewdefs['Forecasts']['base']['view']['forecastsFilter'] = array(
    'panels' => array(
        0 => array(
            'label' => 'LBL_PANEL_1',
            'fields' => array(
                array(
                    'name' => 'ranges',
                    /*
                    This is an enum field, however the 'options' string is set dynamically in the view (which is why it
                    is missing here), since the dropdown shown to the user depends on a config setting
                    */
                    'type' => 'enum',
                    'multi' => true,
                    'label' => 'LBL_FILTERS',
                    'default' => false,
                    'enabled' => true,
                ),
            ),
        ),
    )
);