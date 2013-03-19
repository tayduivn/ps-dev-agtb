<?php
$layout = MetaDataManager::getLayout('DashboardLayout', array(
    'columns' => 1,
    'name' => 'My Dashboard',
));
$layout->push(0, array(
    array(
        'name' => 'My Account',
        'view' => 'dashablelist',
        'context' => array(
            'module' => 'Accounts',
            'dashlet' => array(
                'type' => 'dashablelist',
                'module' => 'Accounts',
                'display_columns' => array(
                    'name', 'billing_address_country', 'billing_address_city'
                ),
                'my_items' => '1',
            )
        )
    ),
));
$viewdefs['Contacts']['base']['layout']['list-dashboard'] = $layout->getLayout();
