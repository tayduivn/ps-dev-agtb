<?php

$layout = MetaDataManager::getLayout('SideBarLayout');
$layout->push('main', array(
    'layout' => array(
        'type' => 'drawer',
        'showEvent' => array(
            "delegate" => true,
            "event" => "click [name=create_button]",
        ),
        'components' => array(
            array(
                'layout' => 'create',
                'context' => array(
                    'create' => true,
                ),
            )
        )
    ),
));
$layout->push('main', array('view' => 'headerpane'));
$layout->push('main', array('layout' => 'list'));
$layout->push('main', array('view' => 'list-bottom'));
$layout->push('side', array('layout' => 'list-sidebar'));
$layout->push('preview', array('layout' => 'preview'));
$viewdefs['base']['layout']['records'] = $layout->getLayout();