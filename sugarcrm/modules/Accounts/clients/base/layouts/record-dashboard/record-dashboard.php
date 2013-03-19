<?php
$layout = MetaDataManager::getLayout('DashboardLayout', array(
    'columns' => 1,
    'name' => 'My Dashboard',
));
$layout->push(0, array(
    array(
        'name' => 'Opportunitity Metrics',
        'view' => 'opportunity-metrics',
        'context' => array(
            'module' => 'Accounts',
            'dashlet' => array(
                'type' => 'opportunity-metrics',
            )
        )
    ),
));
$layout->push(0, array(
    array(
        'name' => 'News Feed',
        'view' => 'news',
        'context' => array(
            'dashlet' => array(
                'type' => 'news',
                'limit' => '5'
            )
        )
    ),
));
$layout->push(0, array(
    array(
        'name' => 'Interactions',
        'view' => 'interactions',
        'context' => array(
            'dashlet' => array(
                'type' => 'interactions',
                'filter_duration' => '7',
            )
        )
    ),
));
$viewdefs['Accounts']['base']['layout']['record-dashboard'] = $layout->getLayout();
