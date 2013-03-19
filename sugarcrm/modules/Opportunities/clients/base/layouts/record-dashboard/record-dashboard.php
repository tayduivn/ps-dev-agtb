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
            'name' => 'Interactions Chart',
            'view' => 'interactionschart',
            'context' => array(
                'module' => 'Opportunities',
                'dashlet' => array(
                    'type' => 'interactionschart',
                    'filter_duration' => '7',
                ),
            ),
        ),
    )
);
$layout->push(
    0,
    array(
        array(
            'name' => 'Interactions',
            'view' => 'interactions',
            'context' => array(
                'module' => 'Opportunities',
                'dashlet' => array(
                    'type' => 'interactions',
                    'filter_duration' => '7',
                ),
            ),
        ),
    )
);
$layout->push(
    0,
    array(
        array(
            'name' => 'Attachments',
            'view' => 'attachments',
            'context' => array(
                'module' => 'Notes',
                'model' => '',
                'modelId' => '',
                'dashlet' => array(
                    'type' => 'attachments',
                    'module' => 'Notes',
                    'link' => 'notes',
                    'display_rows' => '5',
                    'auto_refresh' => -1,
                ),
            ),
        ),
    )
);
$viewdefs['Opportunities']['base']['layout']['record-dashboard'] = $layout->getLayout();
