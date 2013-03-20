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
            'name' => 'My Leads',
            'view' => 'dashablelist',
            'context' => array(
                'module' => 'Leads',
                'dashlet' => array(
                    'type' => 'dashablelist',
                    'module' => 'Leads',
                    'display_columns' => array(
                        'full_name',
                        'email',
                        'phone_work',
                        'status',
                    ),
                    'my_items' => '1',
                    'display_rows' => 5,
                ),
            ),
        ),
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

$viewdefs['Tasks']['base']['layout']['list-dashboard'] = $layout->getLayout();
