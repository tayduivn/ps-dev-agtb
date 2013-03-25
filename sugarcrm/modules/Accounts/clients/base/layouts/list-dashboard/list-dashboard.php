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
$viewdefs['Accounts']['base']['layout']['list-dashboard'] = $layout->getLayout();
