<?php
$layout = MetaDataManager::getLayout('SideBarLayout');
$layout->push('main', array('view'=>'record'));
$layout->push('main', array(
        'view' => 'activity',
        'context' => array(
            'link' => 'notes',
        ),
    )
);
$layout->push('main', array(
        'view' => 'editmodal',
        'context' => array(
            'link' => 'notes',
        ),
    )
);
$layout->push('preview', array('layout' => 'preview'));
$viewdefs['portal']['layout']['record'] = $layout->getLayout();
