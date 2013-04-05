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
            'name' => 'Recent Tweets - @SugarCRM',
            'view' => 'twitter',
            'context' => array(
                'dashlet' => array(
                    'name' => 'Recent Tweets - @SugarCRM',
                    'type' => 'twitter',
                    'twitter' => 'sugarcrm',
                    'limit' => '20',
                ),
            ),
        ),
    )
);

$layout->push(
    0,
    array(
        array(
            'name' => 'My Contacts',
            'view' => 'dashablelist',
            'context' => array(
                'module' => 'Contacts',
                'dashlet' => array(
                    'name' => 'My Contacts',
                    'type' => 'dashablelist',
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
            ),
        ),

    )
);
$layout->push(
    1,
    array(
        array(
            'name' => 'Pipeline',
            'view' => 'forecast-pipeline',
            'context' => array(
                'dashlet' => array(
                    'name' => 'Pipeline',
                    'type' => 'forecast-pipeline',
                    'display_type' => 'self'
                ),
            ),
        ),
        array(
            'name' => 'Sales By Country',
            'view' => 'countrychart',
            'context' => array(
                'dashlet' => array(
                    'name' => 'Sales By Country',
                    'type' => 'countrychart',
                ),
            ),
        ),
    )
);
$layout->push(
    1,
    array(
        array(
            'name' => 'Top 10 sales opportunities',
            'view' => 'bubblechart',
            'context' => array(
                'dashlet' => array(
                    'name' => 'Top 10 sales opportunities',
                    'type' => 'bubblechart',
                    'filter_duration' => 0,
                    'filter_assigned' => 'my',
                ),
            ),
        ),
    )
);
$viewdefs['Home']['base']['layout']['record-dashboard'] = $layout->getLayout();
