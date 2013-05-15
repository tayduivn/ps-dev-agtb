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
            'view' => array(
                'name' => 'dashablelist',
                'label' => 'My Assigned Bugs',
                'display_columns' => array(
                    'bug_number',
                    'name',
                    'status',
                ),
                'my_items' => '1',
                'display_rows' => 5,
                'status' => 'Assigned',
            ),
            'context' => array(
                'module' => 'Bugs',
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
                'label' => 'Twitter Dashlet',
                'twitter' => 'sugarcrm',
                'limit' => '5',
            ),
            'context' => array(
                'module' => 'Home',
            )
        ),
    )
);

$viewdefs['Cases']['base']['layout']['list-dashboard'] = $layout->getLayout();
