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
                'name' => 'interactions',
                'label' => 'Interactions',
                'filter_duration' => '7',
            ),
        ),
    )
);
$layout->push(
    0,
    array(
        array(
            'view' => array(
                'name' => 'attachments',
                'label' => 'Attachments',
                'limit' => '5',
                'auto_refresh' => '0',
            ),
            'context' => array(
                'module' => 'Notes',
                'link' => 'notes',
            ),
        ),
    )
);
$viewdefs['Opportunities']['base']['layout']['record-dashboard'] = $layout->getLayout();
