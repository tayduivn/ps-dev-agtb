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
                'label' => 'My Leads',
                'display_columns' => array(
                    'full_name',
                    'email',
                    'phone_work',
                    'status',
                ),
                'my_items' => '1',
                'display_rows' => 5,
            ),
            'context' => array(
                'module' => 'Leads',
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
                'label' => 'My Assigned Bugs',
                'display_columns' => array(
                    'bug_number',
                    'name',
                    'status',
                ),
                'my_items' => '1',
                'display_rows' => 5,
            ),
            'context' => array(
                'module' => 'Bugs',
            ),
        ),
    )
);

$viewdefs['Tasks']['base']['layout']['list-dashboard'] = $layout->getLayout();
