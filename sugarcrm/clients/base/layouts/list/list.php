<?php

$layout = MetaDataManager::getLayout('GenericLayout', array('type' => 'list'));
$layout->push(array(
    'layout'=> array(
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
$layout->push(array('view'=>'headerpane'));
$layout->push(array('layout'=>'subpanel'));
$layout->push(array('view'=>'list-bottom'));
$viewdefs['base']['layout']['list'] = $layout->getLayout();