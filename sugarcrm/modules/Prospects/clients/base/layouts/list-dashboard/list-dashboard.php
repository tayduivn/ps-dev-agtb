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
                'type' => 'My Accounts',
                'display_columns' => array(
                    'name',
                    'billing_address_country',
                    'billing_address_city',
                ),
                'my_items' => '1',
                'display_rows' => 5,
            ),
            'context' => array(
                'module' => 'Accounts',
            ),
        ),
    )
);
$viewdefs['Prospects']['base']['layout']['list-dashboard'] = $layout->getLayout();
