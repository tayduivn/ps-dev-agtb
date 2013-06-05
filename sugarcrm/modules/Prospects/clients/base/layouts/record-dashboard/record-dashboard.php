<?php
$layout = MetaDataManager::getLayout(
    'DashboardLayout',
    array(
         'columns' => 1,
         'name'    => 'My Dashboard',
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
$viewdefs['Prospects']['base']['layout']['record-dashboard'] = $layout->getLayout();
