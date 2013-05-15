<?php
$layout = MetaDataManager::getLayout(
    'DashboardLayout',
    array(
        'name' => 'My Dashboard',
        'columns' => array(
            array(
                "width" => 4,
            ),
            array(
                "width" => 8,
            ),
        ),
    )
);
$layout->push(
    0,
    array(
        array(
            'view' => array(
                'name' => 'twitter',
                'label' => 'Recent Tweets - @SugarCRM',
                'twitter' => 'sugarcrm',
            ),
        ),
    )
);

$layout->push(
    0,
    array(
        array(
            'view' => array(
                'name' => 'dashablelist',
                'label' => 'My Contacts',
                'module' => 'Contacts',
                'display_columns' => array(
                    'full_name',
                    'account_name',
                    'phone_work',
                    'title',
                ),
                'my_items' => '1',
                'display_rows' => 15,
            ),
            'context' => array(
                'module' => 'Contacts',
            ),
        ),

    )
);
$layout->push(
    1,
    array(
        array(
            'view' => array(
                'name' => 'forecast-pipeline',
                'label' => 'Pipeline',
                'display_type' => 'self',
            ),
            'context' => array(
                'module' => 'Forecasts',
            )
        ),
        array(
            'view' => array(
                'name' => 'countrychart',
                'label' => 'Sales By Country',
            ),
        ),
    )
);
$layout->push(
    1,
    array(
        array(

            'view' => array(
                'name' => 'bubblechart',
                'label' => 'Top 10 sales opportunities',
                'filter_duration' => 0,
                'filter_assigned' => 'my',
            ),
        ),
    )
);
$viewdefs['Home']['base']['layout']['record-dashboard'] = $layout->getLayout();
