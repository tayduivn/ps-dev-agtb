<?php
$layout = MetaDataManager::getLayout(
    'DashboardLayout',
    array(
        'columns' => 1,
        'name' => 'My Dashboard',
    )
);
$layout->push(
    0,
    array(
        array(
            'name' => 'My Assigned Bugs',
            'view' => 'dashablelist',
            'context' => array(
                'module' => 'Bugs',
                'dashlet' => array(
                    'name' => 'My Assigned Bugs',
                    'type' => 'dashablelist',
                    'module' => 'Bugs',
                    'display_columns' => array(
                        'bug_number',
                        'name',
                        'status',
                    ),
                    'my_items' => '1',
                    'display_rows' => 5,
                    'status' => 'Assigned',
                ),
            ),
        ),
    )
);
$layout->push(
    0,
    array(
        array(
            'name' => 'Twitter Dashlet',
            'view' => 'twitter',
            'context' => array(
                'dashlet' => array(
                    'name' => 'Twitter Dashlet',
                    'type' => 'twitter',
                    'twitter' => 'sugarcrm',
                    'limit' => '5',
                ),
            ),
        ),
    )
);

$viewdefs['Cases']['base']['layout']['list-dashboard'] = $layout->getLayout();
