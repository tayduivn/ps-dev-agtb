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
             'name'    => 'Interactions',
             'view'    => 'interactions',
             'context' => array(
                 'module'  => 'Prospects',
                 'dashlet' => array(
                     'name'            => 'Interactions',
                     'type'            => 'interactions',
                     'filter_duration' => '7',
                 ),
             ),
         ),
    )
);
$viewdefs['Prospects']['base']['layout']['record-dashboard'] = $layout->getLayout();
