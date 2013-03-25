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
                'module' => 'Contacts',
                'dashlet' => array(
                    'name' => 'Interactions Chart',
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
                'dashlet' => array(
                    'name' => 'Interactions',
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
            'name' => 'News Feed',
            'view' => 'twitter',
            'context' => array(
                'dashlet' => array(
                    'name' => 'News Feed',
                    'type' => 'twitter',
                    'limit' => '5',
                    'requiredModel' => true,
                ),
            ),
        ),
    )
);
$viewdefs['Contacts']['base']['layout']['record-dashboard'] = $layout->getLayout();
