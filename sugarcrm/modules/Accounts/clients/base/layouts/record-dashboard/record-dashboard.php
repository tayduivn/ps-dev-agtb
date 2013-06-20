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
                'name' => 'opportunity-metrics',
                'label' => 'Opportunitity Metrics',
            ),
        ),
    )
);
$layout->push(
    0,
    array(
        array(
            'view' => array(
                'name' => 'casessummary',
                'label' => 'Cases Summary',
            ),
        ),
    )
);
$layout->push(
    0,
    array(
        array(
            'view' => array(
                'name' => 'news',
                'label' => 'News Feed',
            ),
        ),
    )
);
$layout->push(
    0,
    array(
        array(
            'view' => array(
                'name' => 'interactions',
                'label' => 'Interactions',
                'filter_duration' => '7',
            ),
        ),
    )
);
$viewdefs['Accounts']['base']['layout']['record-dashboard'] = $layout->getLayout();
