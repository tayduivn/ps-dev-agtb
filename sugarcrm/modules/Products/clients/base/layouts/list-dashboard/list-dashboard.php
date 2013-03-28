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
                        'title',
                        'phone_work',
                        'date_entered',
                        'assigned_user_name',
                    ),
                    'my_items' => '1',
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
                        'email',
                        'phone_work',
                    ),
                    'my_items' => '1',
                ),
            ),
        ),
    )
);
$viewdefs['Products']['base']['layout']['list-dashboard'] = $layout->getLayout();
