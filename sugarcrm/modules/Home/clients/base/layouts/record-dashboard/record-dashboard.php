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
            'name' => 'My Closed Opportunities',
            'view' => 'dashablelist',
            'context' => array(
                'module' => 'Opportunities',
                'dashlet' => array(
                    'name' => 'My Closed Opportunities',
                    'type' => 'dashablelist',
                    'module' => 'Opportunities',
                    'display_columns' => array(
                        'name',
                        'account_name',
                        'amount',
                        'date_closed',
                        'sales_status',
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
            'name' => 'Twitter Dashlet with SugarCRM',
            'view' => 'twitter',
            'context' => array(
                'dashlet' => array(
                    'name' => 'Twitter Dashlet with SugarCRM',
                    'type' => 'twitter',
                    'twitter' => 'sugarcrm',
                    'limit' => '5',
                ),
            ),
        ),
    )
);
$layout->push(
    1,
    array(
        array(
            'name' => 'My Accounts',
            'view' => 'dashablelist',
            'context' => array(
                'module' => 'Accounts',
                'dashlet' => array(
                    'name' => 'My Accounts',
                    'type' => 'dashablelist',
                    'module' => 'Accounts',
                    'display_columns' => array(
                        'name',
                        'phone_office',
                        'billing_address_country',
                        'billing_address_city',
                    ),
                    'my_items' => '1',
                    'display_rows' => 15
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
                        'account_name',
                        'phone_work',
                        'email',
                    ),
                    'my_items' => '1',
                ),
            ),
        ),
    )
);
$viewdefs['Home']['base']['layout']['record-dashboard'] = $layout->getLayout();
