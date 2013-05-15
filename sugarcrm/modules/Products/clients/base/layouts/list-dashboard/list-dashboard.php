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
                'display_rows' => 5,
            ),
            'context' => array(
                'module' => 'Accounts',
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
                'display_columns' => array(
                    'full_name',
                    'account_name',
                    'email',
                    'phone_work',
                ),
                'my_items' => '1',
            ),

            'context' => array(
                'module' => 'Contacts',
            ),
        ),
    )
);
$viewdefs['Products']['base']['layout']['list-dashboard'] = $layout->getLayout();
