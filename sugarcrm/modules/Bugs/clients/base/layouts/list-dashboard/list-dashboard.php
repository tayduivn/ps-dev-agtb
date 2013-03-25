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
            'name' => 'Twitter Dashlet',
            'view' => 'twitter',
            'context' => array(
                'dashlet' => array(
                    'name' => 'Twitter Dashlet',
                    'type' => 'twitter',
                    'twitter' => 'sugarcrm',
                    'limit' => '5',
                ),
            ),
        ),
    )
);

$viewdefs['Bugs']['base']['layout']['list-dashboard'] = $layout->getLayout();
