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
                'name' => 'twitter',
                'label' => 'Twitter Dashlet',
                'twitter' => 'sugarcrm',
                'limit' => '5',
            ),
            'context' => array(
                'module' => 'Home',
            )
        ),
    )
);

$viewdefs['Bugs']['base']['layout']['list-dashboard'] = $layout->getLayout();
