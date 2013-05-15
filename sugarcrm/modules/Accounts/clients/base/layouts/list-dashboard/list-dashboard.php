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
                'name' => 'countrychart',
                'label' => 'Sales By Country'
            ),
        ),
    )
);
$viewdefs['Accounts']['base']['layout']['list-dashboard'] = $layout->getLayout();
