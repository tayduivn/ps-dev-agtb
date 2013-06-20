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
                'label' => 'My Accounts',
                'display_columns' => array(
                    'name',
                    'billing_address_country',
                    'billing_address_city',
                ),
                'my_items' => '1',
            ),
            'context' => array(
                'module' => 'Accounts',
            ),
        ),
    )
);
$viewdefs['Contacts']['base']['layout']['list-dashboard'] = $layout->getLayout();
