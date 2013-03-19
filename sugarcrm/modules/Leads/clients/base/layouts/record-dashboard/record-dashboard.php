<?php
$layout = MetaDataManager::getLayout('DashboardLayout', array(
    'columns' => 1,
    'name' => 'My Dashboard',
));

$layout->push(0, array(
    array(
        'name' => 'Interactions',
        'view' => 'interactions',
        'context' => array(
            'module' => 'Leads',
            'dashlet' => array(
                'type' => 'interactions',
                'filter_duration' => '7',
            )
        )
    ),
));
$layout->push(0, array(
    array(
        'name' => 'Interactions Chart',
        'view' => 'interactionschart',
        'context' => array(
            'module' => 'Leads',
            'dashlet' => array(
                'type' => 'interactionschart',
                'filter_duration' => '7',
            )
        )
    ),
));
$viewdefs['Leads']['base']['layout']['record-dashboard'] = $layout->getLayout();
