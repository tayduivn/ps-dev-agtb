<?php
$layout = MetaDataManager::getLayout('DashboardLayout', array(
    'name' => 'My Home Dashboard',
    'columns' => array(
        array(
            "width" => 4
        ),
        array(
            "width" => 8
        )
    )
));
$layout->push(0, array(
    array(
        'name' => 'Recently Closed Opportunities',
        'view' => 'dashablelist',
        'context' => array(
            'module' => 'Opportunities',
            'dashlet' => array(
                'type' => 'dashablelist',
                'module' => 'Opportunities',
                'display_columns' => array(
                    'name', 'account_name', 'amount', 'date_closed'
                ),
                'my_items' => '1',
            )
        )
    ),
));
$layout->push(0, array(
    array(
        'name' => 'Twitter Dashlet with SugarCRM',
        'view' => 'twitter',
        'context' => array(
            'dashlet' => array(
                'type' => 'twitter',
                'twitter' => 'sugarcrm',
                'limit' => '5',
            )
        )
    ),
));
$layout->push(1, array(
    array(
        'name' => 'My Account',
        'view' => 'dashablelist',
        'context' => array(
            'module' => 'Accounts',
            'dashlet' => array(
                'type' => 'dashablelist',
                'module' => 'Accounts',
                'display_columns' => array(
                    'name','phone_office', 'billing_address_country'
                ),
                'my_items' => '1',
            )
        )
    ),
    array(
        'name' => 'Sales By Country',
        'view' => 'countrychart',
        'context' => array(
            'dashlet' => array(
                'type' => 'countrychart',
            )
        )
    ),

));
$layout->push(1, array(
    array(
        'name' => 'My Contacts',
        'view' => 'dashablelist',
        'context' => array(
            'module' => 'Contacts',
            'dashlet' => array(
                'type' => 'dashablelist',
                'module' => 'Contacts',
                'display_columns' => array(
                    'full_name', 'title', 'phone_work', 'date_entered', 'assigned_user_name'
                ),
                'my_items' => '1',
            )
        )
    ),
));
$viewdefs['Home']['base']['layout']['record-dashboard'] = $layout->getLayout();
